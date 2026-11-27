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
use App\Events\AlbumSaved;
use App\Events\TagAlbumSaved;
use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\FileDeleterJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Models\Album;
use App\Models\Person;
use App\Models\TagAlbum;
use Illuminate\Database\Query\Builder;
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
		$jobs = [];

		DB::transaction(function () use (&$jobs): void {
			$this->softDelete();
			$jobs = $this->forceDelete();
		});

		// Both softDelete() (detach) and forceDelete() (hard delete) remove
		// `photo_album` rows via raw DB writes, bypassing every photo-level
		// model event. The TagAlbum/PersonAlbum "matching albums" cache (see
		// AlbumRepository::getMatchingAlbumsForPersonPaginated()) depends on
		// those events to know a photo's containing albums changed. A coarse
		// flush is cheap and correctness-safe for this rare, bulk operation.
		AlbumListingCacheFlushRequested::dispatchIf($this->soft_delete_photo_ids !== [] || $this->force_delete_photo_ids !== []);

		return $jobs;
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

		// Chunk to avoid hitting the database placeholder limit (MySQL error 1390).
		// Both photo and album ID arrays can be large, so we chunk each independently
		// and run all cross-product combinations within the same transaction.
		collect($this->soft_delete_photo_ids)->chunk(QueryLimits::ID_CHUNK_SIZE)->each(function ($photo_chunk): void {
			collect($this->album_ids)->chunk(QueryLimits::ID_CHUNK_SIZE)->each(function ($album_chunk) use ($photo_chunk): void {
				DB::table('photo_album')
					->whereIn('photo_id', $photo_chunk->all())
					->whereIn('album_id', $album_chunk->all())
					->delete();
			});
		});
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

		$this->resetCoversAndHeaders();

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
	 * Reset headers and covers pointing to the photos to be force-deleted.
	 *
	 * First capture which albums/tag albums are *currently* referencing any
	 * of those photos as their cover/header — this may be a *different* album
	 * than the one the deletion request originated from, anywhere in the
	 * instance.
	 *
	 * @return void
	 */
	private function resetCoversAndHeaders(): void
	{
		$affected_album_ids = [];
		$affected_tag_album_ids = [];
		foreach (array_chunk(array_values($this->force_delete_photo_ids), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$affected_album_ids = array_merge(
				$affected_album_ids,
				DB::table('albums')
					->where(fn ($q) => $q->whereIn('header_id', $chunk)->orWhereIn('cover_id', $chunk))
					->pluck('id')->all()
			);
			$affected_tag_album_ids = array_merge(
				$affected_tag_album_ids,
				DB::table('tag_albums')->whereIn('cover_id', $chunk)->pluck('id')->all()
			);

			DB::table('albums')->whereIn('header_id', $chunk)->update(['header_id' => null]);
			DB::table('albums')->whereIn('cover_id', $chunk)->update(['cover_id' => null]);
			DB::table('tag_albums')->whereIn('cover_id', $chunk)->update(['cover_id' => null]);
		}

		// Re-read in chunks (FR-112-09): album id => parent id, existing tag album ids.
		$affected_albums = [];
		foreach (array_chunk(array_values(array_unique($affected_album_ids)), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$affected_albums += Album::query()->whereIn('id', $chunk)->pluck('parent_id', 'id')->all();
		}
		AlbumSaved::dispatchIf($affected_albums !== [], array_keys($affected_albums), array_values($affected_albums));
		$affected_tag_album_ids_verified = [];
		foreach (array_chunk(array_values(array_unique($affected_tag_album_ids)), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$affected_tag_album_ids_verified = array_merge($affected_tag_album_ids_verified, TagAlbum::query()->whereIn('id', $chunk)->pluck('id')->all());
		}
		TagAlbumSaved::dispatchIf($affected_tag_album_ids_verified !== [], $affected_tag_album_ids_verified);
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
