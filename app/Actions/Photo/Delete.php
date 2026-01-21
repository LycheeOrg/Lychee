<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Shop\PurchasableService;
use App\Constants\PhotoAlbum as PA;
use App\DTO\Delete\PhotosToBeDeletedDTO;
use App\Events\PhotoDeleted;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\ModelDBException;
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
	private PurchasableService $purchasable_service;

	public function __construct(
	) {
		$this->purchasable_service = resolve(PurchasableService::class);
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
	 *
	 * @throws ModelDBException
	 */
	public function do(array $photo_ids, string|null $from_id): void
	{
		if ($from_id === null) {
			throw new LycheeLogicException('The $from_id must be provided with the $photo_ids.');
		}
		if (count($photo_ids) === 0) {
			return;
		}

		// First find out which photos do not have an album.
		// Those will be deleted.
		$unsorted_photo_ids = DB::table('photos')
			->leftJoin(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->whereIn('photos.id', $photo_ids)
			->whereNull(PA::ALBUM_ID)
			->select(['photos.id'])->pluck('id')->all();

		// Next we find all photos which are in albums.
		$photo_ids = DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photo_ids)->select([PA::PHOTO_ID])->pluck('photo_id')->all();

		// Now we need to figure out those who are in other albums.
		$photo_ids_in_other_albums = DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->where(PA::ALBUM_ID, '!=', $from_id)
			->distinct()
			->select([PA::PHOTO_ID])->pluck('photo_id')->all();

		// Substract those from the list of photos to be deleted the photos which
		// are still in other albums.
		$delete_photo_ids = array_diff($photo_ids, $photo_ids_in_other_albums);

		// Finally, add the unsorted photos to be deleted.
		$delete_photo_ids = array_merge($delete_photo_ids, $unsorted_photo_ids);

		$this->purchasable_service->deleteMulitplePhotoPurchasables($photo_ids, [$from_id]);

		$photos_to_be_deleted = new PhotosToBeDeletedDTO(
			force_delete_photo_ids: $delete_photo_ids,
			soft_delete_photo_ids: $photo_ids,
			album_ids: [$from_id],
		);
		$jobs = $photos_to_be_deleted->executeDelete();

		// Dispatch events for affected albums to trigger recomputation
		PhotoDeleted::dispatch($from_id);

		foreach ($jobs as $job) {
			dispatch($job);
		}
	}
}