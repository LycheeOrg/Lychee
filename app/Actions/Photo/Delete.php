<?php

namespace App\Actions\Photo;

use App\Image\FileDeleter;
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
class Delete
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
	 * The method does not delete the associated files from the physical
	 * storage.
	 * Instead, the method returns an object in which all these files have
	 * been collected.
	 * This object can (and must) be used to eventually delete the files,
	 * however doing so can be deferred.
	 *
	 * @param string[] $photoIds the photo IDs
	 *
	 * @return FileDeleter contains the collected files which became obsolete
	 */
	public function do(array $photoIds): FileDeleter
	{
		$this->collectSizeVariantPathsByPhotoID($photoIds);
		$this->collectSymLinksByPhotoID($photoIds);
		$this->deleteDBRecords($photoIds);

		return $this->fileDeleter;
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array $photoIds the photo IDs
	 *
	 * @return void
	 */
	private function collectSizeVariantPathsByPhotoID(array $photoIds): void
	{
		$svShortPaths = SizeVariant::query()
			->from('size_variants as sv')
			->select(['sv.short_path'])
			->join('photos as p', 'p.id', '=', 'sv.photo_id')
			->leftJoin('photos as dup', function (JoinClause $join) use ($photoIds) {
				$join
					->on('dup.checksum', '=', 'p.checksum')
					->whereNotIn('dup.id', $photoIds);
			})
			->whereIn('p.id', $photoIds)
			->whereNull('dup.id')
			->pluck('sv.short_path');
		$this->fileDeleter->addRegularFiles($svShortPaths);
	}

	/**
	 * Collects all symbolic links which shall be deleted from disk.
	 *
	 * @param array $photoIds
	 *
	 * @return void
	 */
	private function collectSymLinksByPhotoID(array $photoIds): void
	{
		$symLinkPaths = SymLink::query()
			->select(['sym_links.short_path'])
			->join('size_variants', 'size_variants.id', '=', 'sym_links.size_variant_id')
			->whereIn('size_variants.photo_id', $photoIds)
			->pluck('sym_links.short_path');
		$this->fileDeleter->addSymbolicLinks($symLinkPaths);
	}

	/**
	 * Deletes the records from DB.
	 *
	 * The records are deleted in such an order that foreign keys are not
	 * broken.
	 *
	 * @param array $photoIds
	 *
	 * @return void
	 */
	private function deleteDBRecords(array $photoIds): void
	{
		SymLink::query()
			->whereExists(function (BaseBuilder $query) use ($photoIds) {
				$query
					->from('size_variants', 'sv')
					->whereColumn('id', '=', 'sym_links.size_variant_id')
					->whereIn('photo_id', $photoIds);
			})
			->delete();
		SizeVariant::query()
			->whereIn('size_variants.photo_id', $photoIds)
			->delete();
		Photo::query()
			->whereIn('id', $photoIds)
			->delete();
	}
}
