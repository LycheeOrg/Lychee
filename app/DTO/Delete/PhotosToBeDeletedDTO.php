<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Delete;

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Jobs\FileDeleterJob;
use App\Models\SizeVariant;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PhotosToBeDeletedDTO
{
	/**
	 * Container for all Albums and associated Tracks to be deleted.
	 *
	 * @param string[] $force_delete_photo_ids the photo IDs to be force deleted => removed from storage etc
	 * @param string[] $soft_delete_photo_ids  the photos IDs to be soft deleted => only the link between the album and IDs to be removed
	 * @param string[] $album_ids              the IDs of all albums to be deleted (including descendants)
	 *
	 * @return void
	 */
	public function __construct(
		public array $force_delete_photo_ids,
		public array $soft_delete_photo_ids,
		public array $album_ids,
	) {
	}

	/**
	 * Delete the designated photos.
	 * There is no check for album dependencies. This has already be done.
	 * This is a force delete.
	 *
	 * @return array
	 */
	public function executeDelete(): array
	{
		$delete_jobs = [];

		DB::transaction(function () use (&$delete_jobs): void {
			$this->softDelete();
			$delete_jobs = $this->forceDelete();
		});

		return $delete_jobs;
	}

	/**
	 * Soft delete = just remove the links between photos and albums.
	 *
	 * @return void
	 */
	private function softDelete(): void
	{
		if (count($this->soft_delete_photo_ids) === 0) {
			return;
		}

		// Just remove the link between albums and photos.
		DB::table('photo_album')
			->whereIn('photo_id', $this->soft_delete_photo_ids)
			->whereIn('album_id', $this->album_ids)
			->delete();
	}

	/**
	 * Execute the force deletion of photos and associated data.
	 *
	 * @return FileDeleterJob[] Jobs to be executed for the file deletions
	 */
	private function forceDelete(): array
	{
		// We do not check the purchasable service as they have already been deleted by the caller.
		if (count($this->force_delete_photo_ids) === 0) {
			return [];
		}

		// Reset headers and covers pointing to deleted photos
		DB::table('albums')->whereIn('header_id', $this->force_delete_photo_ids)->update(['header_id' => null]);
		DB::table('albums')->whereIn('cover_id', $this->force_delete_photo_ids)->update(['cover_id' => null]);

		// Maybe consider doing multiple queries for the different storage types.
		$exclude_size_variants_ids = DB::table('order_items')->select(['size_variant_id'])->pluck('size_variant_id')->all();

		// Collect size variants to be deleted
		// ! Risk of memory exhaustion if too many photos are deleted at once !
		$size_variants_local = $this->collectSizeVariantPathsByPhotoID($this->force_delete_photo_ids, StorageDiskType::LOCAL, $exclude_size_variants_ids);
		$short_paths_local = $size_variants_local->pluck('short_path')->all();
		$short_path_watermarked_local = $size_variants_local->pluck('short_path_watermarked')->filter()->all();

		$size_variants_s3 = $this->collectSizeVariantPathsByPhotoID($this->force_delete_photo_ids, StorageDiskType::S3, $exclude_size_variants_ids);
		$short_paths_s3 = $size_variants_s3->pluck('short_path')->all();
		$short_path_watermarked_s3 = $size_variants_s3->pluck('short_path_watermarked')->filter()->all();

		$live_photo_short_paths_local = $this->collectLivePhotoPathsByPhotoID($this->force_delete_photo_ids, StorageDiskType::LOCAL)->pluck('live_photo_short_path')->all();
		$live_photo_short_paths_s3 = $this->collectLivePhotoPathsByPhotoID($this->force_delete_photo_ids, StorageDiskType::S3)->pluck('live_photo_short_path')->all();

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

		// Those we are keeping.
		DB::table('size_variants')->whereIn('id', $exclude_size_variants_ids)->update(['photo_id' => null]);
		// Those we delete
		DB::table('size_variants')->whereIn('photo_id', $this->force_delete_photo_ids)->delete();
		DB::table('statistics')->whereIn('photo_id', $this->force_delete_photo_ids)->delete();
		DB::table('palettes')->whereIn('photo_id', $this->force_delete_photo_ids)->delete();
		DB::table('photo_album')->whereIn('photo_id', $this->force_delete_photo_ids)->delete(); // Just to be sure.
		DB::table('photos')->whereIn('id', $this->force_delete_photo_ids)->delete();

		return $delete_jobs;
	}

	/**
	 * Collects all short paths of size variants which shall be deleted from
	 * disk.
	 *
	 * Size variants which belong to a photo which has a duplicate that is
	 * not going to be deleted are skipped.
	 *
	 * @param array<int,string> $photo_ids                 the photo IDs
	 * @param StorageDiskType   $storage_disk              the storage disk to filter for (null = all)
	 * @param array<int,string> $exclude_size_variants_ids size variant IDs to be excluded
	 *
	 * @return Collection<int,SizeVariant> the size variants to be deleted
	 */
	private function collectSizeVariantPathsByPhotoID(array $photo_ids, StorageDiskType $storage_disk, array $exclude_size_variants_ids): Collection
	{
		if (count($photo_ids) === 0) {
			return collect([]);
		}

		return SizeVariant::query()
			->from('size_variants as sv')
			->select(['sv.short_path', 'sv.short_path_watermarked'])
			->join('photos as p', 'p.id', '=', 'sv.photo_id')
			->whereIn('p.id', $photo_ids)
			->where('sv.storage_disk', '=', $storage_disk->value)
			->whereNotIn('sv.id', $exclude_size_variants_ids)
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
