<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Actions\Photo\Delete as PhotoDelete;
use App\Actions\Shop\PurchasableService;
use App\Constants\AccessPermissionConstants as APC;
use App\Constants\PhotoAlbum as PA;
use App\DTO\Delete\AlbumsToBeDeleteDTO;
use App\Enum\StorageDiskType;
use App\Events\AlbumDeleted;
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
		$tag_albums_ids = DB::table('tag_albums')->whereIn('id', $album_ids)->select('id')->pluck('id')->all();

		$this->deleteTagAlbums($tag_albums_ids);

		$album_ids = array_diff($album_ids, $tag_albums_ids);
		// Nothing else to do. Woop woop.
		if (count($album_ids) === 0) {
			return;
		}

		// Validate the tree before deleting anything
		$this->validateBeforeDelete();

		// Now can handle the regular albums
		$albums_to_delete = $this->findAllAlbumsToDelete($album_ids);

		$this->jobs[] = new FileDeleterJob(StorageDiskType::LOCAL, $albums_to_delete->tracks->all());

		$photos_to_delete = $this->findAllPhotosToDelete($albums_to_delete->album_ids);

		// Nuke the photos.
		$photo_delete_action = resolve(PhotoDelete::class);
		$jobs = $photo_delete_action->forceDelete($photos_to_delete);

		// Nuke the albums.
		$albums_to_delete->executeDelete();

		foreach ($jobs as $job) {
			$this->jobs[] = $job;
		}

		// Dispatch events for parent albums to trigger recomputation
		foreach ($albums_to_delete->parent_ids as $parent_id) {
			AlbumDeleted::dispatch($parent_id);
		}

		// Dispatch the file deletion jobs
		foreach ($this->jobs as $job) {
			dispatch($job);
		}
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
		$purchasable_service = resolve(PurchasableService::class);
		$purchasable_service->deleteMultipleAlbumPurchasables($tag_album_ids);
		DB::table('live_metrics')->whereIn('album_id', $tag_album_ids)->delete();
		DB::table(APC::ACCESS_PERMISSIONS)->whereIn(APC::BASE_ALBUM_ID, $tag_album_ids)->delete();
		DB::table('statistics')->whereIn('album_id', $tag_album_ids)->delete();
		DB::table('tag_albums')->whereIn('id', $tag_album_ids)->delete();
		DB::table('base_albums')->whereIn('id', $tag_album_ids)->delete();
	}

	/**
	 * We fetch all the photos that actually need to be deleted.
	 *
	 * @param string[] $album_ids that are being deleted
	 *
	 * @return string[] of photo IDs that can be deleted fully
	 */
	public function findAllPhotosToDelete(array $album_ids): array
	{
		// First collect which pictures needs to be potentially deleted.
		// ! RISK OF MEMOY EXHAUSTION !
		/** @var Collection<int,object{photo_id:string,album_count:int}> $photos_ids_occurances_in_album */
		$photos_ids_occurances_in_album = DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::ALBUM_ID, $album_ids)
			->select([PA::PHOTO_ID, DB::raw('COUNT(*) AS album_count')])
			->groupBy(PA::PHOTO_ID)
			->orderBy(PA::PHOTO_ID, 'asc')
			->get();

		// Potential photos to be deleted
		$photos_ids = $photos_ids_occurances_in_album->pluck('photo_id')->all();

		// We select all the photos which are impacted: we want to know if they are only occuring in those albums or not.
		// Note that photos which are only in the deleted albums can be deleted fully.
		/** @var Collection<int,object{photo_id:string,album_count:int}> $photos_ids_occurances */
		$photos_ids_occurances = DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::PHOTO_ID, $photos_ids)
			->select([PA::PHOTO_ID, DB::raw('COUNT(*) AS album_count')])
			->groupBy(PA::PHOTO_ID)
			->orderBy(PA::PHOTO_ID, 'asc')
			->get();

		// Now we need to zip those two collections to determine which photos can be deleted fully.
		$photos_to_delete_fully = [];
		$photos_to_detach = [];
		$idx_in_album = 0;
		$idx_total = 0;
		$count_in_album = $photos_ids_occurances_in_album->count();
		$count_total = $photos_ids_occurances->count();
		while ($idx_in_album < $count_in_album && $idx_total < $count_total) {
			$photo_in_album = $photos_ids_occurances_in_album[$idx_in_album];
			$photo_total = $photos_ids_occurances[$idx_total];
			if ($photo_in_album->photo_id !== $photo_total->photo_id) {
				// This should never happen
				if ($photo_in_album->photo_id < $photo_total->photo_id) {
					$idx_in_album++;
				} else {
					$idx_total++;
				}
				continue;
			}

			$occ_in_deleting_albums = intval($photo_in_album->album_count);
			$photo_total_count = intval($photo_total->album_count);
			if ($photo_total_count === $occ_in_deleting_albums) {
				$photos_to_delete_fully[] = $photo_in_album->photo_id;
			} else {
				$photos_to_detach[] = $photo_in_album->photo_id;
			}
			$idx_in_album++;
			$idx_total++;
		}

		return $photos_to_delete_fully;
	}

	/**
	 * Find all albums we want to delete (sub trees).
	 *
	 * @param array $album_ids
	 *
	 * @return AlbumsToBeDeleteDTO
	 */
	private function findAllAlbumsToDelete(array $album_ids): AlbumsToBeDeleteDTO
	{
		// First gather the gaps that will be made:
		$gaps = $this->getGaps($album_ids);

		// Only regular albums are owners of photos, so we only need to
		// find all photos in those and their descendants
		// Only load necessary attributes for tree.
		/** @var Collection<int,object{id:string,parent_id:string|null,_lft:int,_rgt:int,track_short_path:string|null}> $albums */
		$albums = DB::table('albums')
			->select(['id', 'parent_id', '_lft', '_rgt', 'track_short_path'])
			->whereIn('id', $album_ids)
			->get();

		// Collect unique parent IDs BEFORE deletion for event dispatching
		$parent_ids = $albums->pluck('parent_id')->filter()->unique()->values()->all();

		$recursive_album_ids = $albums->pluck('id')->all(); // only IDs which refer to regular albums are incubators for recursive IDs
		$recursive_album_tracks = $albums->pluck('track_short_path');

		/** @var Album $album */
		foreach ($albums as $album) {
			// Collect all (aka recursive) sub-albums in each album
			// Use DB::table directly to avoid any Eloquent overhead and eager loading
			$sub_albums = DB::table('albums')
				->select(['id', 'track_short_path'])
				->whereBetween('_lft', [$album->_lft + 1, $album->_rgt - 1])
				->get();
			$recursive_album_ids = array_merge($recursive_album_ids, $sub_albums->pluck('id')->all());
			$recursive_album_tracks = $recursive_album_tracks->merge($sub_albums->pluck('track_short_path'));
		}
		// prune the null values
		$recursive_album_tracks = $recursive_album_tracks->filter(fn ($val) => $val !== null);

		return new AlbumsToBeDeleteDTO(
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
		/** @var Collection<int,object{_lft:int,_rgt:int}> $albums */
		$albums = DB::table('albums')->whereIn('id', $album_ids)->select(['_lft', '_rgt'])->get();
		foreach ($albums as $album) {
			$gaps[] = [
				'lft' => $album->_lft,
				'rgt' => $album->_rgt,
			];
		}

		return $gaps;
	}
}