<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\StructOfArrays;

use App\Actions\Search\PhotoSearch;
use App\Constants\PhotoAlbum as PA;
use App\DTO\Search\SearchToken;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the base photo query for the v3 search tiers (Feature 069), shared
 * by {@see QuerySearchPhotos} and {@see QuerySearchPhotoDetails}.
 *
 * Structural counterpart of
 * {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource} — a class rather
 * than a trait, because unlike an album-backed source this one has no
 * {@see \App\Contracts\Models\AbstractAlbum} to hang off and is therefore worth
 * resolving and testing on its own.
 *
 * The search *predicate* is entirely {@see PhotoSearch}' existing strategy
 * registry, reused verbatim (spec.md NG4); only the projection differs.
 */
class SearchPhotoSource
{
	public function __construct(
		private readonly PhotoSearch $photo_search,
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * Base query for the search result, carrying exactly one row per distinct
	 * `photos.id` (FR-069-05).
	 *
	 * `PhotoQueryPolicy::applySearchabilityFilter()` left-joins `photo_album`
	 * and `albums` without a `distinct()`, so a photo belonging to N albums
	 * fans out to N rows — v2 counts those duplicates into its `total`
	 * (Q-069-08). Rather than bolt `distinct()` onto a query that also carries
	 * the policy's joined columns, the whole filtered query is demoted to an
	 * id-subquery and re-entered as a plain `whereIn` — the same technique
	 * `ResolvesPhotoSource`'s `BaseSmartAlbum` branch already documents and
	 * uses for the identical fan-out.
	 *
	 * @param array<int,SearchToken> $tokens
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	public function query(array $tokens, ?Album $origin): FixedQueryBuilder
	{
		$matching_ids = $this->photo_search
			->sqlQuery($tokens, $origin, with_relations: false)
			->select('photos.id');

		// PhotoBuilder extends FixedQueryBuilder, so this satisfies the
		// declared return type without narrowing.
		return Photo::query()->whereIn('photos.id', $matching_ids);
	}

	/**
	 * Resolves one concrete, viewer-accessible containing album id per photo
	 * (FR-069-04) — load-bearing, since it becomes the `{album_id}` path
	 * segment the v3 Asset endpoint needs to render a cross-album result.
	 *
	 * Mirrors {@see \App\Actions\Map\QueryMapPhotos}'s own resolution
	 * (Q-067-11, reused verbatim per Q-069-07): a separate join-and-collapse
	 * pass, never `Album` hydration and never a per-photo access re-check
	 * (NFR-069-05).
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string>
	 */
	public function resolveAlbumIds(array $photo_ids, ?Album $origin, ?User $user): array
	{
		if (count($photo_ids) === 0) {
			return [];
		}

		return $origin !== null
			? $this->resolveForSubtree($photo_ids, $origin, $user)
			: $this->resolveForRoot($photo_ids, $user);
	}

	/**
	 * Origin-scoped search: candidates are narrowed to `$origin`'s own subtree,
	 * then tie-broken by lowest `_lft`.
	 *
	 * Lying inside an authorized subtree is *not* on its own enough to make an
	 * album accessible: `appendUnreachableAlbumsCondition()` does test the
	 * photo's own album, but `appendSearchabilityConditions()` OR-s that whole
	 * test away for a photo the viewer owns. So a viewer-owned photo can be
	 * admitted while one of its in-subtree memberships is an album the viewer
	 * cannot enter at all — and naming that album in `album_ids[i]` would
	 * 403 every Asset URL built from it (FR-069-04). The same accessibility
	 * predicate {@see self::resolveForRoot()} applies is therefore applied
	 * here too, with the same admin short-circuit.
	 *
	 * A photo left with no accessible in-subtree album falls back to the
	 * `unsorted` smart album in {@see QuerySearchPhotos}, which is where the
	 * owner escape above puts it anyway (Q-069-11).
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string>
	 */
	private function resolveForSubtree(array $photo_ids, Album $origin, ?User $user): array
	{
		$query = DB::table(PA::PHOTO_ALBUM)
			->join('albums', 'albums.id', '=', PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->where('albums._lft', '>=', $origin->_lft)
			->where('albums._rgt', '<=', $origin->_rgt);

		if ($user?->may_administrate !== true) {
			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
			$query->join('base_albums', 'base_albums.id', '=', PA::ALBUM_ID);
			$this->album_query_policy->joinSubComputedAccessPermissions($query, PA::ALBUM_ID, 'left', '', false, $user);
			$query->where(fn ($q) => $this->album_query_policy->appendAccessibilityConditions($q, $user, $unlocked_album_ids));
		}

		$rows = $query
			->orderBy(PA::PHOTO_ID)
			->orderBy('albums._lft')
			->select([PA::PHOTO_ID . ' as photo_id', PA::ALBUM_ID . ' as album_id'])
			->get();

		return $this->firstPerPhoto($rows);
	}

	/**
	 * Unscoped search: there is no natural containing album, so the candidate
	 * set is every album the photo belongs to, narrowed to those the viewer
	 * can actually access, collapsed by `GROUP BY` + `MIN()`. An admin
	 * short-circuits the accessibility join entirely — every album is
	 * accessible to them — mirroring every other policy method's own admin
	 * bypass.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string>
	 */
	private function resolveForRoot(array $photo_ids, ?User $user): array
	{
		if ($user?->may_administrate === true) {
			$rows = DB::table(PA::PHOTO_ALBUM)
				->whereIn('photo_id', $photo_ids)
				->groupBy('photo_id')
				->selectRaw('photo_id, MIN(album_id) as album_id')
				->get();

			return $this->firstPerPhoto($rows);
		}

		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$query = DB::table(PA::PHOTO_ALBUM)
			->join('base_albums', 'base_albums.id', '=', PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids);

		$this->album_query_policy->joinSubComputedAccessPermissions($query, PA::ALBUM_ID, 'left', '', false, $user);
		$query->where(fn ($q) => $this->album_query_policy->appendAccessibilityConditions($q, $user, $unlocked_album_ids));

		$rows = $query
			->groupBy(PA::PHOTO_ID)
			->selectRaw(PA::PHOTO_ID . ' as photo_id, MIN(' . PA::ALBUM_ID . ') as album_id')
			->get();

		return $this->firstPerPhoto($rows);
	}

	/**
	 * @param \Illuminate\Support\Collection<int,\stdClass> $rows
	 *
	 * @return array<string,string>
	 */
	private function firstPerPhoto(\Illuminate\Support\Collection $rows): array
	{
		$result = [];
		foreach ($rows as $row) {
			if (!array_key_exists($row->photo_id, $result)) {
				$result[$row->photo_id] = $row->album_id;
			}
		}

		return $result;
	}
}
