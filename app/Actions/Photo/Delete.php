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
		$fileDeleter = new FileDeleter();

		// Get all short paths of size variants which belong to photos
		// which are going to be deleted.
		// But exclude those short paths which are duplicated by a size
		// variant of a photo which is not going to be deleted.
		$svShortPaths = SizeVariant::query()
			->from('size_variants as sv')
			->select(['sv.short_path'])
			->leftJoin('size_variants as dup', function (JoinClause $join) use ($photoIds) {
				$join
					->on('dup.short_path', '=', 'sv.short_path')
					->whereColumn('dup.id', '<>', 'sv.id')
					->whereNotIn('dup.photo_id', $photoIds);
			})
			->whereIn('sv.photo_id', $photoIds)
			->whereNull('dup.id')
			->pluck('sv.short_path');
		$fileDeleter->addRegularFiles($svShortPaths);

		// Get all short paths of symbolic links which point to size variants
		// which are going to be deleted
		$symLinkPaths = SymLink::query()
			->select(['sym_links.short_path'])
			->join('size_variants', 'size_variants.id', '=', 'sym_links.size_variant_id')
			->whereIn('size_variants.photo_id', $photoIds)
			->pluck('sym_links.short_path');
		$fileDeleter->addSymbolicLinks($symLinkPaths);

		// Delete records from DB in "inverse" order to not break foreign keys
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

		return $fileDeleter;
	}
}
