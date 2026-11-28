<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Actions\Shared\DispatchAfterDelete;
use App\Actions\Shop\PurchasableService;
use App\Constants\AccessPermissionConstants as APC;
use App\Constants\PersonAlbumPersons;
use App\Constants\PhotoAlbum as PA;
use App\Constants\QueryLimits;
use App\DTO\Delete\AlbumsToBeDeletedDTO;
use App\DTO\Delete\PhotosToBeDeletedDTO;
use App\Enum\StorageDiskType;
use App\Events\AlbumDeleted;
use App\Events\BaseAlbumRemoved;
use App\Exceptions\CorruptedTreeException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Jobs\CheckTreeState;
use App\Jobs\FileDeleterJob;
use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
class Delete
{
	private array $jobs = [];

	/**
	 * Deletes the designated albums (tag albums and regular albums) from the DB.
	 *
	 * The method only deletes the records for albums, photos, their size variants.
	 * The method does not delete the associated files from the physical storage.
	 * Instead, the method returns an object in which all these files have been collected.
	 * This object can (and must) be used to eventually delete the files, however doing so can be deferred.
	 *
	 * @param string[] $album_ids the album IDs (contains IDs of regular _and_ tag albums)
	 *
	 * @return void contains the collected files which became obsolete
	 *
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 * @throws UnauthenticatedException
	 */
	public function do(array $album_ids): void
	{
		// De-duplicated, then every query binds at most one chunk (FR-112-09).
		$album_ids = array_values(array_unique($album_ids));

		$person_albums_ids = $this->idsIn('person_albums', $album_ids);

		$this->deletePersonAlbums($person_albums_ids);

		$album_ids = array_values(array_diff($album_ids, $person_albums_ids));

		$tag_albums_ids = $this->idsIn('tag_albums', $album_ids);

		$this->deleteTagAlbums($tag_albums_ids);

		$album_ids = array_values(array_diff($album_ids, $tag_albums_ids));
		// Nothing else to do. Woop woop.
		if (count($album_ids) === 0) {
			return;
		}

		// Validate the tree before deleting anything
		$this->validateBeforeDelete();

		// Now can handle the regular albums
		$albums_to_delete = $this->findAllAlbumsToDelete($album_ids);

		// Track files of one disk are split into bounded jobs (FR-112-03).
		$albums_to_delete->tracks
			->groupBy(fn (object $track) => $track->disk)
			->each(function (Collection $group, string $disk): void {
				array_push($this->jobs, ...FileDeleterJob::batches(StorageDiskType::from($disk), $group->pluck('file_name')->all()));
			});

		$photos_to_be_deleted = $this->findAllPhotosToDelete($albums_to_delete->album_ids);

		// Nuke the photos.
		$jobs = $photos_to_be_deleted->executeDelete();

		// Nuke the albums.
		$albums_to_delete->executeDelete();

		foreach ($jobs as $job) {
			$this->jobs[] = $job;
		}

		// Dispatch events for parent albums to trigger recomputation
		foreach ($albums_to_delete->parent_ids as $parent_id) {
			AlbumDeleted::dispatch($parent_id);
		}

		// File, person-stats and face-embedding jobs: after the response on `sync` (FR-112-04).
		DispatchAfterDelete::dispatch($this->jobs);
	}

	/**
	 * Delete tag albums and dependencies.
	 *
	 * @param array $tag_album_ids
	 *
	 * @return void
	 */
	private function deleteTagAlbums(array $tag_album_ids): void
	{
		if (count($tag_album_ids) === 0) {
			return;
		}

		$purchasable_service = resolve(PurchasableService::class);
		$purchasable_service->deleteMultipleAlbumPurchasables($tag_album_ids);
		foreach (array_chunk($tag_album_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			DB::table('live_metrics')->whereIn('album_id', $chunk)->delete();
			DB::table(APC::ACCESS_PERMISSIONS)->whereIn(APC::BASE_ALBUM_ID, $chunk)->delete();
			DB::table('statistics')->whereIn('album_id', $chunk)->delete();
			DB::table('tag_albums_tags')->whereIn('album_id', $chunk)->delete();
			DB::table('album_user_thumbs')->whereIn('album_id', $chunk)->delete();
			DB::table('tag_albums')->whereIn('id', $chunk)->delete();
			DB::table('base_albums')->whereIn('id', $chunk)->delete();
		}

		BaseAlbumRemoved::dispatch($tag_album_ids);
	}

	/**
	 * Delete person albums and dependencies.
	 *
	 * @param array $person_album_ids
	 *
	 * @return void
	 */
	private function deletePersonAlbums(array $person_album_ids): void
	{
		if (count($person_album_ids) === 0) {
			return;
		}

		$purchasable_service = resolve(PurchasableService::class);
		$purchasable_service->deleteMultipleAlbumPurchasables($person_album_ids);
		foreach (array_chunk($person_album_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			DB::table('live_metrics')->whereIn('album_id', $chunk)->delete();
			DB::table(APC::ACCESS_PERMISSIONS)->whereIn(APC::BASE_ALBUM_ID, $chunk)->delete();
			DB::table('statistics')->whereIn('album_id', $chunk)->delete();
			DB::table(PersonAlbumPersons::PERSON_ALBUM_PERSONS)->whereIn(PersonAlbumPersons::ALBUM_ID, $chunk)->delete();
			DB::table('album_user_thumbs')->whereIn('album_id', $chunk)->delete();
			DB::table('person_albums')->whereIn('id', $chunk)->delete();
			DB::table('base_albums')->whereIn('id', $chunk)->delete();
		}

		BaseAlbumRemoved::dispatch($person_album_ids);
	}

	/**
	 * Ids of $table among $ids, queried in chunks.
	 *
	 * @param string[] $ids
	 *
	 * @return string[]
	 */
	private function idsIn(string $table, array $ids): array
	{
		$found = [];
		foreach (array_chunk($ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$found = array_merge($found, DB::table($table)->whereIn('id', $chunk)->pluck('id')->all());
		}

		return $found;
	}

	/**
	 * We fetch all the photos that actually need to be deleted.
	 *
	 * @param string[] $album_ids that are being deleted
	 *
	 * @return PhotosToBeDeletedDTO of photo IDs that can be deleted fully
	 */
	public function findAllPhotosToDelete(array $album_ids): PhotosToBeDeletedDTO
	{
		$album_ids = array_values(array_unique($album_ids));

		// photo id => number of its links into the deleted albums, summed over
		// the album chunks (the chunks do not overlap), and photo id => number
		// of all its links. Both are keyed by photo id (FR-112-09).
		// ! RISK OF MEMOY EXHAUSTION !
		$links_in_deleted_albums = [];
		$links_total = [];
		foreach (array_chunk($album_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			/** @var Collection<int,object{photo_id:string,album_count:int}> $in_chunk */
			$in_chunk = DB::table(PA::PHOTO_ALBUM)
				->whereIn(PA::ALBUM_ID, $chunk)
				->select([PA::PHOTO_ID, DB::raw('COUNT(*) AS album_count')])
				->groupBy(PA::PHOTO_ID)
				->get();
			foreach ($in_chunk as $row) {
				$links_in_deleted_albums[$row->photo_id] = ($links_in_deleted_albums[$row->photo_id] ?? 0) + intval($row->album_count);
			}

			// Total links of the photos of this chunk. A subquery instead of
			// whereIn($photo_ids) keeps the bindings to the album chunk.
			/** @var Collection<int,object{photo_id:string,album_count:int}> $totals */
			$totals = DB::table(PA::PHOTO_ALBUM)
				->whereIn(PA::PHOTO_ID, function ($query) use ($chunk): void {
					$query->select(PA::PHOTO_ID)
						->from(PA::PHOTO_ALBUM)
						->whereIn(PA::ALBUM_ID, $chunk);
				})
				->select([PA::PHOTO_ID, DB::raw('COUNT(*) AS album_count')])
				->groupBy(PA::PHOTO_ID)
				->get();
			foreach ($totals as $row) {
				$links_total[$row->photo_id] = intval($row->album_count);
			}
		}

		// Photos which are only in the deleted albums can be deleted fully,
		// the others are only detached from them.
		$photos_to_delete_fully = [];
		$photos_to_detach = [];
		foreach ($links_in_deleted_albums as $photo_id => $count) {
			if (($links_total[$photo_id] ?? 0) === $count) {
				$photos_to_delete_fully[] = strval($photo_id);
			} else {
				$photos_to_detach[] = strval($photo_id);
			}
		}

		return new PhotosToBeDeletedDTO(
			force_delete_photo_ids: $photos_to_delete_fully,
			soft_delete_photo_ids: $photos_to_detach,
			album_ids: $album_ids,
		);
	}

	/**
	 * Find all albums we want to delete (sub trees).
	 *
	 * @param array $album_ids
	 *
	 * @return AlbumsToBeDeletedDTO
	 */
	private function findAllAlbumsToDelete(array $album_ids): AlbumsToBeDeletedDTO
	{
		// First gather the gaps that will be made:
		$gaps = $this->getGaps($album_ids);

		// Only regular albums are owners of photos, so we only need to
		// find all photos in those and their descendants
		// Only load necessary attributes for tree.
		/** @var Collection<int,object{id:string,parent_id:string|null,_lft:int,_rgt:int}> $albums */
		$albums = collect(array_chunk($album_ids, QueryLimits::ID_CHUNK_SIZE))->flatMap(
			fn (array $chunk) => DB::table('albums')
				->select(['id', 'parent_id', '_lft', '_rgt'])
				->whereIn('id', $chunk)
				->get()
		);

		// Collect unique parent IDs BEFORE deletion for event dispatching
		$parent_ids = $albums->pluck('parent_id')->filter()->unique()->values()->all();

		$recursive_album_ids = $albums->pluck('id')->all(); // only IDs which refer to regular albums are incubators for recursive IDs

		/** @var Album $album */
		foreach ($albums as $album) {
			// Collect all (aka recursive) sub-albums in each album
			// Use DB::table directly to avoid any Eloquent overhead and eager loading
			$sub_albums = DB::table('albums')
				->select(['id'])
				->whereBetween('_lft', [$album->_lft + 1, $album->_rgt - 1])
				->get();
			$recursive_album_ids = array_merge($recursive_album_ids, $sub_albums->pluck('id')->all());
		}
		// A sub-album selected together with an ancestor is listed once, so
		// that its photo links are counted once (FR-112-09).
		$recursive_album_ids = array_values(array_unique($recursive_album_ids));

		// All tracks (across every disk) belonging to the recursive album set (FR-055-12).
		// Chunk album_ids to avoid hitting the database placeholder limit (MySQL error 1390).
		/** @var Collection<int,object{disk:string,file_name:string}> $recursive_album_tracks */
		$recursive_album_tracks = collect($recursive_album_ids)->chunk(QueryLimits::ID_CHUNK_SIZE)->reduce(
			function (Collection $carry, Collection $chunk): Collection {
				return $carry->concat(
					DB::table('tracks')->select(['disk', 'file_name'])->whereIn('album_id', $chunk->all())->get()
				);
			},
			collect([])
		);

		return new AlbumsToBeDeletedDTO(
			parent_ids: $parent_ids,
			album_ids: $recursive_album_ids,
			tracks: $recursive_album_tracks,
			gaps: $gaps
		);
	}

	/**
	 * Validates that the album tree is correct before deleting albums.
	 * This will avoid issues later.
	 *
	 * @return void
	 *
	 * @throws CorruptedTreeException if the album tree is corrupted
	 */
	public function validateBeforeDelete(): void
	{
		$check = new CheckTreeState();
		$stats = $check->handle();
		if ((($stats['oddness'] ?? 0) > 0) ||
			(($stats['duplicates'] ?? 0) > 0) ||
			(($stats['wrong_parent'] ?? 0) > 0) ||
			(($stats['missing_parent'] ?? 0) > 0)) {
			throw new CorruptedTreeException('Cannot delete albums: album tree is corrupted.');
		}
	}

	/**
	 * Gather the gaps that need to be made.
	 *
	 * @param string[] $album_ids
	 *
	 * @return array{lft:int,rgt:int}[] an array of gaps to be collapsed
	 */
	public function getGaps(array $album_ids): array
	{
		$gaps = [];
		foreach (array_chunk($album_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			/** @var Collection<int,object{_lft:int,_rgt:int}> $albums */
			$albums = DB::table('albums')->whereIn('id', $chunk)->select(['_lft', '_rgt'])->get();
			foreach ($albums as $album) {
				$gaps[] = [
					'lft' => $album->_lft,
					'rgt' => $album->_rgt,
				];
			}
		}

		return $gaps;
	}
}