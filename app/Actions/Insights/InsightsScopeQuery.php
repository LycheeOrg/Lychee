<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\DTO\Insights\InsightsScope;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Applies an Insights scope to queries (Feature 085, FR-085-04, FR-085-16).
 *
 * Owner scope filters `owner_id`; album scope keeps the photos linked to an
 * album of the tree (`EXISTS`, so a photo in two sub-albums counts once)
 * and the albums of the tree.
 */
final class InsightsScopeQuery
{
	/**
	 * @param Builder $query a query over `photos` (or joined to it as `photos`)
	 */
	public static function photos(Builder $query, InsightsScope $scope): Builder
	{
		if ($scope->isAlbum()) {
			return $query->whereExists(fn (Builder $exists) => $exists
				->select(DB::raw(1))
				->from('photo_album as scope_photo_album')
				->join('albums as scope_albums', 'scope_albums.id', '=', 'scope_photo_album.album_id')
				->whereColumn('scope_photo_album.photo_id', '=', 'photos.id')
				->whereBetween('scope_albums._lft', [$scope->album_left, $scope->album_right]));
		}

		return $scope->owner_id === null ? $query : $query->where('photos.owner_id', '=', $scope->owner_id);
	}

	/**
	 * IDs of the photos of an album scope, ascending, in pages of at most
	 * `$chunk` (Feature 108, NFR-108-01).
	 *
	 * Each page is an ordered range over `photo_album.photo_id`; the link's
	 * album is tested by a correlated scalar subquery on its primary key,
	 * which the planner runs per link instead of turning it into a semi-join
	 * that it materialises and sorts again for every page. Every query binds
	 * three values whatever the size of the tree. A photo linked to several
	 * albums of the tree appears once: duplicates inside a page are dropped
	 * and the next page starts after the page's last ID.
	 *
	 * @return \Generator<int,string[]>
	 */
	public static function photoIdPages(InsightsScope $scope, int $chunk): \Generator
	{
		$album_left = DB::table('albums as scope_albums')
			->select('scope_albums._lft')
			->whereColumn('scope_albums.id', '=', 'photo_album.album_id');
		$album_left = DB::raw('(' . $album_left->toSql() . ')');
		$last = '';
		do {
			$page = DB::table('photo_album')
				->where('photo_album.photo_id', '>', $last)
				->whereBetween($album_left, [$scope->album_left, $scope->album_right])
				->orderBy('photo_album.photo_id')
				->limit($chunk)
				->pluck('photo_album.photo_id')
				->all();
			$last = $page[count($page) - 1] ?? $last;

			yield array_values(array_unique($page));
		} while (count($page) === $chunk);
	}

	/**
	 * @param Builder $query a query over `albums` joined to `base_albums`
	 */
	public static function albums(Builder $query, InsightsScope $scope): Builder
	{
		if ($scope->isAlbum()) {
			return $query->whereBetween('albums._lft', [$scope->album_left, $scope->album_right]);
		}

		return $scope->owner_id === null ? $query : $query->where('base_albums.owner_id', '=', $scope->owner_id);
	}
}
