<?php

namespace App\Actions\Album;

use App\Actions\Photo\Delete as PhotoDelete;
use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Image\FileDeleter;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\TagAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Query\Builder as BaseBuilder;

/**
 * Deletes the albums with the designated IDs **efficiently**.
 *
 * This class deliberately violates the principle of separations of concerns.
 * In an ideal world, the method would simply call `->delete()` on every
 * `Album` model, the `Album` model would take care of deleting its
 * sub-albums and every album in turn would take care of deleting its photos.
 * But this is extremely inefficient due to Laravel's architecture:
 *
 *  - Models are heavyweight god classes such that every instance also carries
 *    the whole code for serialization/deserialization
 *  - Models are active records (and don't use the unit-of-work pattern), i.e.
 *    every deletion of a model directly triggers a DB operation; they are
 *    not deferred into a batch operation
 *
 * Moreover, while removing the records for albums and photos from the
 * DB can be implemented rather efficiently, the actual file operations may
 * take some time.
 * Especially, if the files are not stored locally but on a remote file system.
 * Hence, this method collects all files which need to be removed.
 * The caller can then decide to delete them asynchronously.
 */
class Delete extends Action
{
	/**
	 * Deletes the designated albums from the DB.
	 *
	 * The method only deletes the records for albums, photos, their size
	 * variants and potentially associated symbolic links from the DB.
	 * The method does not delete the associated files from the physical
	 * storage.
	 * Instead, the method returns an object in which all these files have
	 * been collected.
	 * This object can (and must) be used to eventually delete the files,
	 * however doing so can be deferred.
	 *
	 * @param string[] $albumIDs the album IDs
	 *
	 * @return FileDeleter contains the collected files which became obsolete
	 *
	 * @throws ModelDBException
	 */
	public function do(array $albumIDs): FileDeleter
	{
		try {
			$unsortedPhotoIDs = [];
			$recursiveAlbumIDs = $albumIDs;

			// Among the smart albums, the unsorted album is special,
			// because it provides deletion of photos
			if (in_array(UnsortedAlbum::ID, $albumIDs, true)) {
				$query = UnsortedAlbum::getInstance()->photos();
				if (!AccessControl::is_admin()) {
					$query->where('owner_id', '=', AccessControl::id());
				}
				$unsortedPhotoIDs = $query->pluck('id')->all();
			}

			// Only regular albums are owners of photos, so we only need to
			// find all photos in those and their descendants
			// Only load necessary attributes for tree; in particular avoid
			// loading expensive `min_taken_at` and `max_taken_at`.
			$albums = Album::query()
				->without(['cover', 'thumb'])
				->select(['id', 'parent_id', '_lft', '_rgt', 'track_short_path'])
				->findMany($albumIDs);

			$recursiveAlbumTracks = $albums->pluck('track_short_path');

			/** @var Album $album */
			foreach ($albums as $album) {
				// Collect all (aka recursive) sub-albums in each album
				$subAlbums = $album->descendants()->select(['id', 'track_short_path'])->get();
				$recursiveAlbumIDs = array_merge($recursiveAlbumIDs, $subAlbums->pluck('id')->all());
				$recursiveAlbumTracks = $recursiveAlbumTracks->merge($subAlbums->pluck('track_short_path'));
			}

			// Delete the photos from DB and obtain the list of files which need
			// to be deleted later
			$fileDeleter = (new PhotoDelete())->do($unsortedPhotoIDs, $recursiveAlbumIDs);
			$fileDeleter->addRegularFiles($recursiveAlbumTracks);

			// Remove descendants of each album which is going to be deleted
			// This is ugly as hell and copy & pasted from
			// \Kalnoy\Nestedset\NodeTrait
			// I really liked the code of master@0199212 ways better, but it was
			// simply too inefficient

			// This code also fixes a bug when more than one album with
			// sub-albums is deleted, i.e. if we delete a "sub-forest".
			// The original code (of the nested set model) updates the
			// (lft,rgt)-indices on the DB level for every single deletion.
			// However, this way deletion of the second albums fails, if the
			// second album has already been hydrated earlier, because the
			// indices of the already hydrated models and the indices in the
			// DB are out-of-sync.
			// Either all remaining models needs to be re-hydrated aka
			// "refreshed" from the (already updated) DB after every single
			// deletion or the update of the DB needs to be postponed until
			// all models have been deleted.
			// The latter is more efficient, because we do not reload models
			// from the DB.

			/** @var array<array{lft: int, rgt:int}> $pendingGapsToMake */
			$pendingGapsToMake = [];
			$deleteQuery = Album::query();
			// First collect all albums to delete in a single query and
			// memorize which indices need to be updated later.
			foreach ($albums as $album) {
				$pendingGapsToMake[] = [
					'lft' => $album->getLft(),
					'rgt' => $album->getRgt(),
				];
				$deleteQuery->whereDescendantOf($album, 'or', false, true);
			}
			// For MySQL deletion must be done in correct order otherwise the
			// foreign key constraint to `parent_id` fails.
			$deleteQuery->orderBy('_lft', 'desc')->delete();
			// _After all_ albums have been deleted, remove the gaps which
			// have been created by the removed albums.
			// Note, the gaps must be removed beginning with the highest
			// values first otherwise the later indices won't be correct.
			// To save some DB queries, we could implement a "makeMultiGap".
			usort($pendingGapsToMake, fn ($a, $b) => $b['lft'] <=> $a['lft']);
			foreach ($pendingGapsToMake as $pendingGap) {
				$height = $pendingGap['rgt'] - $pendingGap['lft'] + 1;
				$album->newNestedSetQuery()->makeGap($pendingGap['rgt'] + 1, -$height);
				Album::$actionsPerformed++;
			}

			TagAlbum::query()->whereIn('id', $albumIDs)->delete();

			// Note, we may need to delete more base albums than those whose
			// ID is in `$albumIDs`.
			// As we might have deleted more regular albums as part of a subtree
			// we simply delete all base albums who neither have an associated
			// (regular) album or tag album.
			BaseAlbumImpl::query()->whereNotExists(function (BaseBuilder $baseBuilder) {
				$baseBuilder->from('albums')->whereColumn('albums.id', '=', 'base_albums.id');
			})->whereNotExists(function (BaseBuilder $baseBuilder) {
				$baseBuilder->from('tag_albums')->whereColumn('tag_albums.id', '=', 'base_albums.id');
			})->delete();

			return $fileDeleter;
		} catch (QueryBuilderException|InternalLycheeException $e) {
			try {
				// if anything goes wrong, don't leave the tree in an inconsistent state
				Album::query()->fixTree();
			} catch (\Throwable) {
				// Sic! We cannot do anything about the inner exception
			}
			throw ModelDBException::create('albums', 'deleting', $e);
		} catch (\InvalidArgumentException $e) {
			try {
				// if anything goes wrong, don't leave the tree in an inconsistent state
				Album::query()->fixTree();
			} catch (\Throwable) {
				// Sic! We cannot do anything about the inner exception
			}
			assert(false, new \AssertionError('\InvalidArgumentException must not be thrown by ->where', $e->getCode(), $e));
		}
	}
}
