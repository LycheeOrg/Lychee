<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Image\FileDeleter;
use App\Models\Album;
use App\Models\Palette;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Statistics;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

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
	 * The method only deletes the records for photos, their size variants.
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
	 * Note that this methods does not assume the recursion within albums ids.
	 * If albums_ids has children, photos within those children will not be deleted.
	 *
	 * @param string[]    $photo_ids the photo IDs
	 * @param string|null $from_id   the ID of the album from which the photos are deleted
	 * @param string[]    $album_ids the album IDs
	 *
	 * @return FileDeleter contains the collected files which became obsolete
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photo_ids, string|null $from_id, array $album_ids = []): FileDeleter
	{
		$this->validateArguments($photo_ids, $from_id, $album_ids);

		// First find out which photos do not have an album.
		// Those will be deleted.
		$unsorted_photo_ids = $this->collectUnsortedPhotos($photo_ids);
		$photo_ids = $this->collectPhotosInAlbums($photo_ids);

		if (count($photo_ids) > 0) {
			// We delete the photos which are in the from and listed in albums.
			DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photo_ids)->where(PA::ALBUM_ID, '=', $from_id)->delete();
		} else {
			$photo_ids = $this->collectPhotosInAlbumsByAlbumID($album_ids);
			// We delete the photos which are in the from and listed in albums.
			DB::table(PA::PHOTO_ALBUM)->whereIn(PA::ALBUM_ID, $album_ids)->delete();
		}

		// Now that the relation is destroyed, we need to figure out which photos really need to be deleted:
		// Those are the ones that were previously in albums but not anymore.
		$photo_ids = $this->collectUnsortedPhotos($photo_ids);
		$photo_ids = array_merge($photo_ids, $unsorted_photo_ids);

		try {
			$this->collectSizeVariantPathsByPhotoID($photo_ids);
			$this->collectLivePhotoPathsByPhotoID($photo_ids);
			$this->deleteDBRecords($photo_ids, $album_ids);
			// @codeCoverageIgnoreStart
		} catch (QueryBuilderException $e) {
			throw ModelDBException::create('photos', 'deleting', $e);
		}
		// @codeCoverageIgnoreEnd
		Album::query()->whereIn('header_id', $photo_ids)->update(['header_id' => null]);

		return $this->fileDeleter;
	}

	/**
	 * We make sure that only the valid code path are used to ensure the integrity
	 * of the database.
	 *
	 * @param array       $photo_ids
	 * @param string|null $from_id
	 * @param array       $album_ids
	 *
	 * @return void
	 *
	 * @throws LycheeLogicException
	 */
	private function validateArguments(array $photo_ids, string|null $from_id, array $album_ids): void
	{
		match (true) {
			count($photo_ids) !== 0 && count($album_ids) !== 0 => throw new LycheeLogicException('Only one of the arguments [$photo_ids, $album_ids] must be set.'),
			count($photo_ids) !== 0 && $from_id === null => throw new LycheeLogicException('The $from_id must be provided with the $photo_ids.'),
			count($album_ids) !== 0 && $from_id !== null => throw new LycheeLogicException('The $from_id must not be provided with the $album_ids.'),
			default => null, // do nothing :)
		};
	}

	/**
	 * We select all photos which are not in an album and in the preselection.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[] Photo IDs
	 */
	private function collectUnsortedPhotos(array $photo_ids): array
	{
		if (count($photo_ids) === 0) {
			return [];
		}

		return Photo::query()->leftJoin(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->whereIn('photos.id', $photo_ids)
			->whereNull(PA::ALBUM_ID)
			->select(['photos.id'])->toBase()->pluck('id')->all();
	}

	/**
	 * We select all the photos which are in an album and in the preselection.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[] photo IDs
	 */
	private function collectPhotosInAlbums(array $photo_ids): array
	{
		if (count($photo_ids) === 0) {
			return [];
		}

		return DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photo_ids)->select([PA::PHOTO_ID])->pluck('photo_id')->all();
	}

	/**
	 * We select all the photos which are in a list of albums.
	 *
	 * @param string[] $album_ids
	 *
	 * @return string[] photo IDs
	 */
	private function collectPhotosInAlbumsByAlbumID(array $album_ids): array
	{
		if (count($album_ids) === 0) {
			return [];
		}

		return DB::table(PA::PHOTO_ALBUM)->whereIn(PA::ALBUM_ID, $album_ids)->select([PA::PHOTO_ID])->pluck('photo_id')->all();
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array<int,string> $photo_ids the photo IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSizeVariantPathsByPhotoID(array $photo_ids): void
	{
		try {
			if (count($photo_ids) === 0) {
				return;
			}

			// Maybe consider doing multiple queries for the different storage types.
			$size_variants = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.storage_disk'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->leftJoin('photos as dup', function (JoinClause $join) use ($photo_ids): void {
					$join
						->on('dup.checksum', '=', 'p.checksum')
						->whereNotIn('dup.id', $photo_ids);
				})
				->whereIn('p.id', $photo_ids)
				->whereNull('dup.id')
				->get();
			$this->fileDeleter->addSizeVariants($size_variants);
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
	 * @param array<int,string> $photo_ids the photo IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function collectLivePhotoPathsByPhotoID(array $photo_ids)
	{
		try {
			if (count($photo_ids) === 0) {
				return;
			}

			$live_photo_short_paths = Photo::query()
				->from('photos as p')
				->select(['p.live_photo_short_path', 'sv.storage_disk'])
				->join('size_variants as sv', function (JoinClause $join): void {
					$join
						->on('sv.photo_id', '=', 'p.id')
						->where('sv.type', '=', SizeVariantType::ORIGINAL);
				})
				->leftJoin('photos as dup', function (JoinClause $join) use ($photo_ids): void {
					$join
						->on('dup.live_photo_checksum', '=', 'p.live_photo_checksum')
						->whereNotIn('dup.id', $photo_ids);
				})
				->whereIn('p.id', $photo_ids)
				->whereNull('dup.id')
				->whereNotNull('p.live_photo_short_path')
				->get(['p.live_photo_short_path', 'sv.storage_disk']);

			$live_variants_short_paths_grouped = $live_photo_short_paths->groupBy('storage_disk');
			$live_variants_short_paths_grouped->each(
				fn ($live_variants_short_paths, $disk) => $this->fileDeleter->addFiles($live_variants_short_paths->map(fn ($lv) => $lv->live_photo_short_path), $disk)
			);
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
	 * @param array<int,string> $photo_ids the photo IDs
	 * @param array<int,string> $album_ids the album IDs
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function deleteDBRecords(array $photo_ids, array $album_ids): void
	{
		try {
			if (count($photo_ids) !== 0) {
				SizeVariant::query()
					->whereIn('size_variants.photo_id', $photo_ids)
					->delete();
			}
			if (count($album_ids) !== 0) {
				SizeVariant::query()
					->whereExists(function (BaseBuilder $query) use ($album_ids): void {
						$query
							->from('photos', 'p')
							->whereColumn('p.id', '=', 'size_variants.photo_id')
							->leftJoin(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'p.id')
							->whereIn(PA::ALBUM_ID, $album_ids);
					})
					->delete();
			}
			if (count($photo_ids) !== 0) {
				Statistics::query()
					->whereIn('photo_id', $photo_ids)
					->delete();
			}
			if (count($album_ids) !== 0) {
				Statistics::query()
					->whereExists(function (BaseBuilder $query) use ($album_ids): void {
						$query
							->from('photos', 'p')
							->whereColumn('p.id', '=', 'statistics.photo_id')
							->leftJoin(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'p.id')
							->whereIn(PA::ALBUM_ID, $album_ids);
					})
					->delete();
			}
			if (count($photo_ids) !== 0) {
				Palette::query()
					->whereIn('photo_id', $photo_ids)
					->delete();
			}
			if (count($album_ids) !== 0) {
				Palette::query()
					->whereExists(function (BaseBuilder $query) use ($album_ids): void {
						$query
							->from('photos', 'p')
							->whereColumn('p.id', '=', 'palettes.photo_id')
							->whereIn('p.album_id', $album_ids);
					})
					->delete();
			}
			if (count($photo_ids) !== 0) {
				Photo::query()->whereIn('id', $photo_ids)->delete();
			}
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}