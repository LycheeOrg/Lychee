<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Constants\PersonAlbumPersons as PAP;
use App\Constants\QueryLimits;
use App\Enum\SmartAlbumType;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Resolves which cached album thumbs (`album_user_thumbs`) a change to the
 * given photos may affect (FR-112-08).
 *
 * - Every smart album: smart albums have no reverse tag/person index, and
 *   each refresh is a cheap single-row lookup.
 * - Forward: tag albums sharing a tag with the photos, and person albums
 *   sharing a non-dismissed person: the photos may now be (re-ranked)
 *   candidates for their thumb.
 * - Backward: tag and person albums currently caching one of the photos as
 *   thumb, whether or not they still match: a replacement may be needed.
 *
 * Photo ids are bound in chunks of {@link QueryLimits::ID_CHUNK_SIZE}; tags,
 * persons and albums are reached through subqueries and joins, so no query
 * binds more than one chunk (FR-112-09).
 */
class AlbumUserThumbTargets
{
	/**
	 * @param array<int,string> $photo_ids
	 *
	 * @return list<array{0:string,1:string}> (kind, album_id) pairs, each at most once
	 */
	public function resolve(array $photo_ids): array
	{
		$photo_ids = array_values(array_unique($photo_ids));
		if ($photo_ids === []) {
			return [];
		}

		$targets = [];
		foreach (SmartAlbumType::cases() as $smart_album_type) {
			$this->add($targets, RecomputeAlbumUserThumbsJob::KIND_SMART, [$smart_album_type->value]);
		}

		$chunks = array_chunk($photo_ids, QueryLimits::ID_CHUNK_SIZE);
		foreach ($chunks as $chunk) {
			$this->add($targets, RecomputeAlbumUserThumbsJob::KIND_TAG, $this->tagAlbumsSharingATag($chunk));
		}
		foreach ($chunks as $chunk) {
			$this->add($targets, RecomputeAlbumUserThumbsJob::KIND_PERSON, $this->personAlbumsSharingAPerson($chunk));
		}
		foreach ($chunks as $chunk) {
			$this->add($targets, RecomputeAlbumUserThumbsJob::KIND_TAG, $this->albumsCaching($chunk, 'tag_albums'));
			$this->add($targets, RecomputeAlbumUserThumbsJob::KIND_PERSON, $this->albumsCaching($chunk, 'person_albums'));
		}

		return array_values($targets);
	}

	/**
	 * @param array<int,array{0:string,1:string}> $targets
	 *
	 * @return list<RecomputeAlbumUserThumbsJob>
	 */
	public function jobs(array $targets): array
	{
		return array_values(array_map(fn (array $target) => new RecomputeAlbumUserThumbsJob($target[0], $target[1]), $targets));
	}

	/**
	 * @param array<int,array{0:string,1:string}> $targets
	 */
	public function dispatch(array $targets): void
	{
		foreach ($targets as [$kind, $album_id]) {
			RecomputeAlbumUserThumbsJob::dispatch($kind, $album_id);
		}
	}

	/**
	 * @param array<string,array{0:string,1:string}> $targets   keyed by "kind|album_id"
	 * @param iterable<string>                       $album_ids
	 */
	private function add(array &$targets, string $kind, iterable $album_ids): void
	{
		foreach ($album_ids as $album_id) {
			$targets[$kind . '|' . $album_id] = [$kind, $album_id];
		}
	}

	/**
	 * @param array<int,string> $photo_ids
	 *
	 * @return array<int,string>
	 */
	private function tagAlbumsSharingATag(array $photo_ids): array
	{
		return DB::table('tag_albums_tags')
			->whereIn('tag_id', fn (Builder $query) => $query->select('tag_id')->from('photos_tags')->whereIn('photo_id', $photo_ids))
			->distinct()
			->pluck('album_id')
			->all();
	}

	/**
	 * @param array<int,string> $photo_ids
	 *
	 * @return array<int,string>
	 */
	private function personAlbumsSharingAPerson(array $photo_ids): array
	{
		return DB::table(PAP::PERSON_ALBUM_PERSONS)
			->whereIn(PAP::PERSON_ID, fn (Builder $query) => $query->select('person_id')
				->from('faces')
				->whereIn('photo_id', $photo_ids)
				->whereNotNull('person_id')
				->where('is_dismissed', '=', false))
			->distinct()
			->pluck(PAP::ALBUM_ID)
			->all();
	}

	/**
	 * Albums of the given table (tag_albums or person_albums) which cache one
	 * of the photos as thumb.
	 *
	 * @param array<int,string> $photo_ids
	 *
	 * @return array<int,string>
	 */
	private function albumsCaching(array $photo_ids, string $album_table): array
	{
		return DB::table('album_user_thumbs')
			->join($album_table, $album_table . '.id', '=', 'album_user_thumbs.album_id')
			->whereIn('album_user_thumbs.photo_id', $photo_ids)
			->distinct()
			->pluck('album_user_thumbs.album_id')
			->all();
	}
}
