<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Shop\PurchasableService;
use App\Constants\PhotoAlbum as PA;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Events\PhotoDeleted;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Jobs\FileDeleterJob;
use App\Models\SizeVariant;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
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
	 * Delete the designated photos.
	 * There is no check for album dependencies. This has already be done.
	 * This is a force delete.
	 *
	 * HOWEVER, the size_variants may not be deleted directly if they are used in other photos.
	 * TODO: deal with the deduplication logic on the file level. That was a mistake.
	 *
	 * @param array $photo_ids Photos to deleted
	 *
	 * @return array
	 */
	public function forceDelete(array $photo_ids): array
	{
		// We do not check the purchasable service as they have already been deleted by the caller.
		if (count($photo_ids) === 0) {
			return [];
		}

		// Reset headers and covers pointing to deleted photos
		DB::table('albums')->whereIn('header_id', $photo_ids)->update(['header_id' => null]);
		DB::table('albums')->whereIn('cover_id', $photo_ids)->update(['cover_id' => null]);

		// Collect size variants to be deleted
		// ! Risk of memory exhaustion if too many photos are deleted at once !
		$size_variants_local = $this->collectSizeVariantPathsByPhotoID($photo_ids, StorageDiskType::LOCAL);
		$short_paths_local = $size_variants_local->pluck('short_path')->all();
		$short_path_watermarked_local = $size_variants_local->pluck('short_path_watermarked')->filter()->all();

		$size_variants_s3 = $this->collectSizeVariantPathsByPhotoID($photo_ids, StorageDiskType::S3);
		$short_paths_s3 = $size_variants_s3->pluck('short_path')->all();
		$short_path_watermarked_s3 = $size_variants_s3->pluck('short_path_watermarked')->filter()->all();

		$live_photo_short_paths_local = $this->collectLivePhotoPathsByPhotoID($photo_ids, StorageDiskType::LOCAL)->pluck('live_photo_short_path')->all();
		$live_photo_short_paths_s3 = $this->collectLivePhotoPathsByPhotoID($photo_ids, StorageDiskType::S3)->pluck('live_photo_short_path')->all();

		$delete_jobs = [];
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::LOCAL, $short_paths_local);
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::LOCAL, $short_path_watermarked_local);
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::LOCAL, $live_photo_short_paths_local);
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::S3, $short_paths_s3);
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::S3, $short_path_watermarked_s3);
		$delete_jobs[] = new FileDeleterJob(StorageDiskType::S3, $live_photo_short_paths_s3);

		// Now delete DB records
		// ! If we are deleting more than a few 1000 photos at once, we may run into
		// ! SQL query size limits. In that case, we need to chunk the deletion.
		DB::table('size_variants')->whereIn('photo_id', $photo_ids)->delete();
		DB::table('statistics')->whereIn('photo_id', $photo_ids)->delete();
		DB::table('palettes')->whereIn('photo_id', $photo_ids)->delete();
		DB::table('photo_album')->whereIn('photo_id', $photo_ids)->delete(); // Just to be sure.
		DB::table('photos')->whereIn('id', $photo_ids)->delete();

		return $delete_jobs;
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

		// We delete the photos which are in the from and listed in albums.
		DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $photo_ids)->where(PA::ALBUM_ID, '=', $from_id)->delete();

		$this->purchasable_service->deleteMulitplePhotoPurchasables($photo_ids, [$from_id]);
		$jobs = $this->forceDelete($delete_photo_ids);

		// Dispatch events for affected albums to trigger recomputation
		PhotoDeleted::dispatch($from_id);

		foreach ($jobs as $job) {
			dispatch($job);
		}
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array<int,string> $photo_ids    the photo IDs
	 * @param StorageDiskType   $storage_disk the storage disk to filter for (null = all)
	 *
	 * @return Collection<int,SizeVariant> the size variants to be deleted
	 *
	 * @throws QueryBuilderException
	 */
	private function collectSizeVariantPathsByPhotoID(array $photo_ids, StorageDiskType $storage_disk): Collection
	{
		if (count($photo_ids) === 0) {
			return collect([]);
		}

		// Maybe consider doing multiple queries for the different storage types.
		$exclude_ids = DB::table('order_items')->select(['size_variant_id'])->pluck('size_variant_id')->all();

		// Maybe consider doing multiple queries for the different storage types.
		return SizeVariant::query()
			->from('size_variants as sv')
			->select(['sv.short_path', 'sv.short_path_watermarked'])
			->join('photos as p', 'p.id', '=', 'sv.photo_id')
			->whereIn('p.id', $photo_ids)
			->where('sv.storage_disk', '=', $storage_disk->value)
			->whereNotIn('sv.id', $exclude_ids)
			->toBase()
			->get();
	}

	/**
	 * Collects all short paths of live photos which shall be deleted from
	 * disk.
	 *
	 * Live photos which have a duplicate that is not going to be deleted are
	 * skipped.
	 *
	 * @param array<int,string> $photo_ids    the photo IDs
	 * @param StorageDiskType   $storage_disk the storage disk to filter for (null = all)
	 *
	 * @return Collection<int,object{live_photo_short_path:string}> the live photo short paths to be deleted
	 *
	 * @throws QueryBuilderException
	 */
	private function collectLivePhotoPathsByPhotoID(array $photo_ids, StorageDiskType $storage_disk): Collection
	{
		if (count($photo_ids) === 0) {
			return collect([]);
		}

		return DB::table('photos', 'p')
			->select(['p.live_photo_short_path'])
			->join('size_variants as sv', function (JoinClause $join): void {
				$join
					->on('sv.photo_id', '=', 'p.id')
					->where('sv.type', '=', SizeVariantType::ORIGINAL);
			})
			->whereIn('p.id', $photo_ids)
			->whereNotNull('p.live_photo_short_path')
			->where('sv.storage_disk', '=', $storage_disk->value)
			->get();
	}
}