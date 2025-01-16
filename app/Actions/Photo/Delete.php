<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Enum\SizeVariantType;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Image\FileDeleter;
use App\Models\Album;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\SymLink;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\JoinClause;

/**
 * Deletes the photos with the designated IDs **efficiently**.
 *
 * This class deliberately violates the principle of separations of concerns.
 * In an ideal world, the method would simply call `->delete()` on every
 * `Photo` model and the `Photo` model would take care of deleting its
 * associated size variants including the media files.
 * But this is extremely inefficient due to Laravel's architecture:
 *
 *  - Models are heavyweight god classes such that every instance also carries
 *    the whole code for serialization/deserialization
 *  - Models are active records (and don't use the unit-of-work pattern), i.e.
 *    every deletion of a model directly triggers a DB operation; they are
 *    not deferred into a batch operation
 *
 * Moreover, while removing the records for photos and size variants from the
 * DB can be implemented rather efficiently, the actual file operations may
 * take some time.
 * Especially, if the files are not stored locally but on a remote file system.
 * Hence, this method collects all files which need to be removed.
 * The caller can then decide to delete them asynchronously.
 */
readonly class Delete
{
	protected FileDeleter $fileDeleter;

	public function __construct()
	{
		$this->fileDeleter = new FileDeleter();
	}

	/**
	 * Deletes the designated photos from the DB.
	 *
	 * The method only deletes the records for photos, their size variants
	 * and potentially associated symbolic links from the DB.
	 * The method does not delete the associated files from physical storage.
	 * Instead, the method returns an object in which all these files have
	 * been collected.
	 * This object can (and must) be used to eventually delete the files,
	 * however doing so can be deferred.
	 *
	 * The method allows deleting individual photos designated by
	 * `$photoIDs` or photos of entire albums designated by `$albumIDs`.
	 * The latter is more efficient, if albums shall be deleted, because
	 * it results in more succinct SQL queries.
	 * Both parameters can be used simultaneously and result in a merged
	 * deletion of the joined set of photos.
	 *
	 * @param string[] $photoIDs the photo IDs
	 * @param string[] $albumIDs the album IDs
	 *
	 * @return FileDeleter contains the collected files which became obsolete
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photoIDs, array $albumIDs = []): FileDeleter
	{
		// TODO: replace this with pipelines, This is typically the kind of pattern.
		try {
			$this->collectSizeVariantPathsByPhotoID($photoIDs);
			$this->collectSizeVariantPathsByAlbumID($albumIDs);
			$this->collectLivePhotoPathsByPhotoID($photoIDs);
			$this->collectLivePhotoPathsByAlbumID($albumIDs);
			$this->collectSymLinksByPhotoID($photoIDs);
			$this->collectSymLinksByAlbumID($albumIDs);
			$this->deleteDBRecords($photoIDs, $albumIDs);
			// @codeCoverageIgnoreStart
		} catch (QueryBuilderException $e) {
			throw ModelDBException::create('photos', 'deleting', $e);
		}
		// @codeCoverageIgnoreEnd
		Album::query()->whereIn('header_id', $photoIDs)->update(['header_id' => null]);

		return $this->fileDeleter;
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array<int,string> $photoIDs the photo IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSizeVariantPathsByPhotoID(array $photoIDs): void
	{
		try {
			if (count($photoIDs) === 0) {
				return;
			}

			// Maybe consider doing multiple queries for the different storage types.
			$sizeVariants = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.storage_disk'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->leftJoin('photos as dup', function (JoinClause $join) use ($photoIDs) {
					$join
						->on('dup.checksum', '=', 'p.checksum')
						->whereNotIn('dup.id', $photoIDs);
				})
				->whereIn('p.id', $photoIDs)
				->whereNull('dup.id')
				->get();
			$this->fileDeleter->addSizeVariants($sizeVariants);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array<int,string> $albumIDs the album IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSizeVariantPathsByAlbumID(array $albumIDs): void
	{
		try {
			if (count($albumIDs) === 0) {
				return;
			}

			// Maybe consider doing multiple queries for the different storage types.
			$sizeVariants = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.storage_disk'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->leftJoin('photos as dup', function (JoinClause $join) use ($albumIDs) {
					$join
						->on('dup.checksum', '=', 'p.checksum')
						->whereNotIn('dup.album_id', $albumIDs);
				})
				->whereIn('p.album_id', $albumIDs)
				->whereNull('dup.id')
				->get();
			$this->fileDeleter->addSizeVariants($sizeVariants);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Collects all short paths of live photos which shall be deleted from
	 * disk.
	 *
	 * Live photos which have a duplicate that is not going to be deleted are
	 * skipped.
	 *
	 * @param array<int,string> $photoIDs the photo IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectLivePhotoPathsByPhotoID(array $photoIDs)
	{
		try {
			if (count($photoIDs) === 0) {
				return;
			}

			$livePhotoShortPaths = Photo::query()
				->from('photos as p')
				->select(['p.live_photo_short_path', 'sv.storage_disk'])
				->join('size_variants as sv', function (JoinClause $join) {
					$join
						->on('sv.photo_id', '=', 'p.id')
						->where('sv.type', '=', SizeVariantType::ORIGINAL);
				})
				->leftJoin('photos as dup', function (JoinClause $join) use ($photoIDs) {
					$join
						->on('dup.live_photo_checksum', '=', 'p.live_photo_checksum')
						->whereNotIn('dup.id', $photoIDs);
				})
				->whereIn('p.id', $photoIDs)
				->whereNull('dup.id')
				->whereNotNull('p.live_photo_short_path')
				->get(['p.live_photo_short_path', 'sv.storage_disk']);

			$liveVariantsShortPathsGrouped = $livePhotoShortPaths->groupBy('storage_disk');
			$liveVariantsShortPathsGrouped->each(
				fn ($liveVariantsShortPaths, $disk) =>
					/** @phpstan-ignore-next-line */
					$this->fileDeleter->addFiles($liveVariantsShortPaths->map(fn ($lv) => $lv->live_photo_short_path), $disk)
			);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Collects all short paths of live photos which shall be deleted from
	 * disk.
	 *
	 * Live photos which have a duplicate that is not going to be deleted are
	 * skipped.
	 *
	 * @param array<int,string> $albumIDs the album IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectLivePhotoPathsByAlbumID(array $albumIDs)
	{
		try {
			if (count($albumIDs) === 0) {
				return;
			}

			$livePhotoShortPaths = Photo::query()
				->from('photos as p')
				->select(['p.live_photo_short_path', 'sv.storage_disk'])
				->join('size_variants as sv', function (JoinClause $join) {
					$join
						->on('sv.photo_id', '=', 'p.id')
						->where('sv.type', '=', SizeVariantType::ORIGINAL);
				})
				->leftJoin('photos as dup', function (JoinClause $join) use ($albumIDs) {
					$join
						->on('dup.live_photo_checksum', '=', 'p.live_photo_checksum')
						->whereNotIn('dup.album_id', $albumIDs);
				})
				->whereIn('p.album_id', $albumIDs)
				->whereNull('dup.id')
				->whereNotNull('p.live_photo_short_path')
				->get(['p.live_photo_short_path', 'sv.storage_disk']);

			$liveVariantsShortPathsGrouped = $livePhotoShortPaths->groupBy('storage_disk');
			$liveVariantsShortPathsGrouped->each(
				/** @phpstan-ignore-next-line */
				fn ($liveVariantsShortPaths, $disk) => $this->fileDeleter->addFiles($liveVariantsShortPaths->map(fn ($lv) => $lv->live_photo_short_path), $disk)
			);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Collects all symbolic links which shall be deleted from disk.
	 *
	 * @param array<int,string> $photoIDs the photo IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSymLinksByPhotoID(array $photoIDs): void
	{
		try {
			if (count($photoIDs) === 0) {
				return;
			}

			$symLinkPaths = SymLink::query()
				->from('sym_links', 'sl')
				->select(['sl.short_path'])
				->join('size_variants as sv', 'sv.id', '=', 'sl.size_variant_id')
				->whereIn('sv.photo_id', $photoIDs)
				->pluck('sl.short_path');
			$this->fileDeleter->addSymbolicLinks($symLinkPaths);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Collects all symbolic links which shall be deleted from disk.
	 *
	 * @param array<int,string> $albumIDs the album IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSymLinksByAlbumID(array $albumIDs): void
	{
		try {
			if (count($albumIDs) === 0) {
				return;
			}

			$symLinkPaths = SymLink::query()
				->from('sym_links', 'sl')
				->select(['sl.short_path'])
				->join('size_variants as sv', 'sv.id', '=', 'sl.size_variant_id')
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->whereIn('p.album_id', $albumIDs)
				->pluck('sl.short_path');
			$this->fileDeleter->addSymbolicLinks($symLinkPaths);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Deletes the records from DB.
	 *
	 * The records are deleted in such an order that foreign keys are not
	 * broken.
	 *
	 * @param array<int,string> $photoIDs the photo IDs
	 * @param array<int,string> $albumIDs the album IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function deleteDBRecords(array $photoIDs, array $albumIDs): void
	{
		try {
			if (count($photoIDs) !== 0) {
				SymLink::query()
					->whereExists(function (BaseBuilder $query) use ($photoIDs) {
						$query
							->from('size_variants', 'sv')
							->whereColumn('sv.id', '=', 'sym_links.size_variant_id')
							->whereIn('photo_id', $photoIDs);
					})
					->delete();
			}
			if (count($albumIDs) !== 0) {
				SymLink::query()
					->whereExists(function (BaseBuilder $query) use ($albumIDs) {
						$query
							->from('size_variants', 'sv')
							->whereColumn('sv.id', '=', 'sym_links.size_variant_id')
							->join('photos', 'photos.id', '=', 'sv.photo_id')
							->whereIn('photos.album_id', $albumIDs);
					})
					->delete();
			}
			if (count($photoIDs) !== 0) {
				SizeVariant::query()
					->whereIn('size_variants.photo_id', $photoIDs)
					->delete();
			}
			if (count($albumIDs) !== 0) {
				SizeVariant::query()
					->whereExists(function (BaseBuilder $query) use ($albumIDs) {
						$query
							->from('photos', 'p')
							->whereColumn('p.id', '=', 'size_variants.photo_id')
							->whereIn('p.album_id', $albumIDs);
					})
					->delete();
			}
			if (count($photoIDs) !== 0) {
				Photo::query()->whereIn('id', $photoIDs)->delete();
			}
			if (count($albumIDs) !== 0) {
				Photo::query()->whereIn('album_id', $albumIDs)->delete();
			}
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
