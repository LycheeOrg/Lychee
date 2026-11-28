<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Constants\PhotoAlbum as PA;
use App\Constants\QueryLimits;
use App\Enum\SmartAlbumType;
use App\Events\AlbumSaved;
use App\Events\TagAlbumSaved;
use App\Models\Album;
use App\Models\Scopes\NotTrashedScope;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\DB;

/**
 * Feature 119 (FR-119-01/02, DO-119-03): removes album links and puts the
 * photos left without any link into the trash (`trashed_photos`).
 *
 * `photo_album` is many-to-many: a photo is trashed only once its last link
 * is removed (owner rule). The decision reads `photo_album` and
 * `trashed_photos` only, never `photos` or `size_variants`, so its cost does
 * not depend on the size of the photo rows (NFR-119-01). The purge deletes
 * the trashed photos later ({@see PurgeTrash}).
 *
 * Every method runs in one transaction and returns the ids it trashed.
 */
final class Trash
{
	/**
	 * Album delete: removes every link to the given albums.
	 *
	 * @param string[] $album_ids
	 *
	 * @return string[] trashed photo ids
	 */
	public function fromAlbums(array $album_ids): array
	{
		$album_ids = array_values(array_unique($album_ids));
		if ($album_ids === []) {
			return [];
		}

		return DB::transaction(function () use ($album_ids): array {
			$photo_ids = [];
			foreach (array_chunk($album_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
				$photo_ids = array_merge($photo_ids, DB::table(PA::PHOTO_ALBUM)->whereIn(PA::ALBUM_ID, $chunk)->distinct()->pluck(PA::PHOTO_ID)->all());
				DB::table(PA::PHOTO_ALBUM)->whereIn(PA::ALBUM_ID, $chunk)->delete();
			}

			return $this->trashUnlinked($photo_ids);
		});
	}

	/**
	 * Photo delete from one album: removes the photos' links to `$from_id`.
	 * With Unsorted no link is removed: the photos without an album are trashed.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[] trashed photo ids
	 */
	public function fromAlbum(array $photo_ids, string $from_id): array
	{
		$photo_ids = array_values(array_unique($photo_ids));
		if ($photo_ids === []) {
			return [];
		}

		return DB::transaction(function () use ($photo_ids, $from_id): array {
			if ($from_id !== SmartAlbumType::UNSORTED->value) {
				foreach (array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
					DB::table(PA::PHOTO_ALBUM)->where(PA::ALBUM_ID, '=', $from_id)->whereIn(PA::PHOTO_ID, $chunk)->delete();
				}
			}

			return $this->trashUnlinked($photo_ids);
		});
	}

	/**
	 * Delete of the photos themselves (NSFW block action, Moderation):
	 * removes every link of the photos.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[] trashed photo ids
	 */
	public function everywhere(array $photo_ids): array
	{
		$photo_ids = array_values(array_unique($photo_ids));
		if ($photo_ids === []) {
			return [];
		}

		return DB::transaction(function () use ($photo_ids): array {
			foreach (array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE) as $chunk) {
				DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $chunk)->delete();
			}

			return $this->trashUnlinked($photo_ids);
		});
	}

	/**
	 * Reset the headers and covers that point to the given photos, anywhere
	 * in the instance, and tell the listeners (FR-119-06).
	 *
	 * @param string[] $photo_ids
	 */
	public function resetCoversAndHeaders(array $photo_ids): void
	{
		$affected_album_ids = [];
		$affected_tag_album_ids = [];
		foreach (array_chunk(array_values($photo_ids), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
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
	 * Trash those of the photos that have no album link left and are not in
	 * the trash yet, then reset the covers and headers pointing to them.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[] trashed photo ids
	 */
	private function trashUnlinked(array $photo_ids): array
	{
		$trashed = [];
		$now = now();
		foreach (array_chunk(array_values(array_unique($photo_ids)), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$linked = DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $chunk)->distinct()->pluck(PA::PHOTO_ID)->all();
			$already = DB::table(NotTrashedScope::TABLE)->whereIn('photo_id', $chunk)->pluck('photo_id')->all();
			$ids = array_values(array_diff($chunk, $linked, $already));
			if ($ids === []) {
				continue;
			}
			DB::table(NotTrashedScope::TABLE)->insert(array_map(fn (string $id) => ['photo_id' => $id, 'trashed_at' => $now], $ids));
			$trashed = array_merge($trashed, $ids);
		}

		if ($trashed !== []) {
			$this->resetCoversAndHeaders($trashed);
		}

		return $trashed;
	}
}
