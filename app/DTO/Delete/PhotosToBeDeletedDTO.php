<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Delete;

use App\Actions\SizeVariant\Delete as SizeVariantDelete;
use App\Constants\QueryLimits;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Events\AlbumListingCacheFlushRequested;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\FileDeleterJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Models\Person;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PhotosToBeDeletedDTO
{
	/**
	 * Photos to delete with their rows and files. Since Feature 119 only the
	 * purge ({@see \App\Actions\Photo\PurgeTrash}) deletes photo rows: the
	 * delete actions put photos into the trash, after removing their album
	 * links ({@see \App\Actions\Photo\Trash}).
	 *
	 * @param string[] $force_delete_photo_ids the photo IDs to be deleted => removed from storage etc
	 *
	 * @return void
	 */
	public function __construct(
		public array $force_delete_photo_ids,
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
		$jobs = [];

		DB::transaction(function () use (&$jobs): void {
			$jobs = $this->forceDelete();
		});

		// forceDelete() removes rows via raw DB writes, bypassing every
		// photo-level model event. The TagAlbum/PersonAlbum "matching albums"
		// cache (see AlbumRepository::getMatchingAlbumsForPersonPaginated())
		// depends on those events. A coarse flush is cheap and
		// correctness-safe for this rare, bulk operation.
		AlbumListingCacheFlushRequested::dispatchIf($this->force_delete_photo_ids !== []);

		return $jobs;
	}

	/**
	 * Execute the force deletion of photos and associated data.
	 *
	 * Each chunk of photos is handled from start to end before the next one
	 * (FR-112-14): its size variants and faces are read once, then its rows
	 * are deleted. The pages of one chunk stay in the DB's buffer pool for all
	 * of its statements, those of the whole set may not (slow disk, small
	 * buffer pool).
	 *
	 * @return (FileDeleterJob|RecomputePersonStatsJob|DeleteFaceEmbeddingsJob)[] Jobs to be dispatched after the commit
	 */
	private function forceDelete(): array
	{
		// We do not check the purchasable service as they have already been deleted by the caller.
		if (count($this->force_delete_photo_ids) === 0) {
			return [];
		}

		// ! Risk of memory exhaustion if too many photos are deleted at once !
		$paths = [];
		$person_ids = [];
		$face_ids = [];
		foreach (array_chunk(array_values($this->force_delete_photo_ids), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			foreach ($this->collectPathsOfChunk($chunk) as $disk => $chunk_paths) {
				$paths[$disk] = array_merge($paths[$disk] ?? [], $chunk_paths);
			}
			$faces = $this->collectFacesOfChunk($chunk);
			$person_ids = array_merge($person_ids, $faces['person_ids']);
			$face_ids = array_merge($face_ids, $faces['face_ids']);
			$this->deleteRowsOfChunk($chunk);
		}

		return self::jobsFor($paths, array_values(array_unique($person_ids)), $face_ids);
	}

	/**
	 * Paths of the files of a chunk of photos, by disk, read in one query.
	 *
	 * - The short path and the watermarked path of every size variant that is
	 *   not sold. A sold variant (referenced by an order item) keeps its file
	 *   for the buyer.
	 * - The live photo, on the disk of the photo's ORIGINAL variant, sold or not.
	 *
	 * Files shared with a same-checksum duplicate that is not deleted are NOT
	 * skipped.
	 *
	 * @param string[] $chunk photo ids
	 *
	 * @return array<string,string[]> disk => paths
	 */
	private function collectPathsOfChunk(array $chunk): array
	{
		/** @var Collection<int,object{type:int|string,storage_disk:string,short_path:string|null,short_path_watermarked:string|null,live_photo_short_path:string|null,is_sold:int|string|null}> $variants */
		$variants = DB::table('size_variants', 'sv')
			->join('photos as p', 'p.id', '=', 'sv.photo_id')
			->whereIn('sv.photo_id', $chunk)
			->select(['sv.type', 'sv.storage_disk', 'sv.short_path', 'sv.short_path_watermarked', 'p.live_photo_short_path'])
			->selectSub(SizeVariantDelete::orderItemOf(DB::query(), 'sv.id')->limit(1), 'is_sold')
			->get();

		$paths = [];
		foreach ($variants as $variant) {
			$is_sold = $variant->is_sold !== null;
			$is_original = intval($variant->type) === SizeVariantType::ORIGINAL->value;
			foreach ([
				$is_sold ? null : $variant->short_path,
				$is_sold ? null : $variant->short_path_watermarked,
				$is_original ? $variant->live_photo_short_path : null,
			] as $path) {
				if ($path !== null && $path !== '') {
					$paths[$variant->storage_disk][] = $path;
				}
			}
		}

		return $paths;
	}

	/**
	 * Faces of a chunk of photos, read in one query: every face id (their
	 * embeddings are deleted) and the persons of the faces that are not
	 * dismissed (their stats are recomputed).
	 *
	 * @param string[] $chunk photo ids
	 *
	 * @return array{person_ids:list<int|string>,face_ids:list<string>}
	 */
	private function collectFacesOfChunk(array $chunk): array
	{
		/** @var Collection<int,object{id:string,person_id:int|string|null,is_dismissed:bool|int|string}> $faces */
		$faces = DB::table('faces')
			->whereIn('photo_id', $chunk)
			->select(['id', 'person_id', 'is_dismissed'])
			->get();

		return [
			'person_ids' => $faces
				->filter(fn (object $face) => $face->person_id !== null && !boolval($face->is_dismissed))
				->pluck('person_id')
				->unique()
				->values()
				->all(),
			'face_ids' => $faces->pluck('id')->values()->all(),
		];
	}

	/**
	 * Delete the rows of a chunk of photos. Sold size variants are kept,
	 * detached from the photo (FR-112-10).
	 *
	 * @param string[] $chunk photo ids
	 *
	 * @return void
	 */
	private function deleteRowsOfChunk(array $chunk): void
	{
		DB::table('size_variants')
			->whereIn('photo_id', $chunk)
			->whereExists(fn (Builder $query) => SizeVariantDelete::orderItemOf($query, 'size_variants.id'))
			->update(['photo_id' => null]);
		// The other variants are deleted.
		DB::table('size_variants')->whereIn('photo_id', $chunk)->delete();
		DB::table('statistics')->whereIn('photo_id', $chunk)->delete();
		DB::table('palettes')->whereIn('photo_id', $chunk)->delete();
		DB::table('nsfw_detections')->whereIn('photo_id', $chunk)->delete();
		DB::table('photo_moderation_events')->whereIn('photo_id', $chunk)->delete();
		DB::table('photo_album')->whereIn('photo_id', $chunk)->delete(); // Just to be sure.
		DB::table('photos')->whereIn('id', $chunk)->delete();
	}

	/**
	 * Jobs to dispatch after the commit: the file deletions of each disk in
	 * bounded batches (FR-112-03), then the person stats (recounted after
	 * their faces have been cascade-deleted) and the face embeddings.
	 *
	 * @param array<string,string[]> $paths      disk => paths
	 * @param list<int|string>       $person_ids
	 * @param list<string>           $face_ids
	 *
	 * @return (FileDeleterJob|RecomputePersonStatsJob|DeleteFaceEmbeddingsJob)[]
	 */
	private static function jobsFor(array $paths, array $person_ids, array $face_ids): array
	{
		$jobs = [];
		foreach (StorageDiskType::cases() as $disk) {
			array_push($jobs, ...FileDeleterJob::batches($disk, $paths[$disk->value] ?? []));
		}
		if ($person_ids !== []) {
			$jobs[] = new RecomputePersonStatsJob($person_ids);
		}
		if ($face_ids !== []) {
			$jobs[] = new DeleteFaceEmbeddingsJob($face_ids);
		}

		return $jobs;
	}
}
