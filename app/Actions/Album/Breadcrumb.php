<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Http\Resources\Models\BreadcrumbItemResource;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Breadcrumb
{
	public function __construct(
		protected readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * @return BreadcrumbItemResource[]
	 */
	public function do(Album $album): array
	{
		$ancestors = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('albums._lft', '<', $album->_lft)
			->where('albums._rgt', '>', $album->_rgt)
			->select(['albums.id', 'base_albums.title', 'base_albums.slug'])
			->orderByDesc('albums._lft')
			->get();

		if ($ancestors->isEmpty()) {
			return [];
		}

		$accessible_ids = $this->getAccessibleAncestorIds($ancestors->pluck('id')->all());

		$result = [];
		foreach ($ancestors as $ancestor) {
			if (!in_array($ancestor->id, $accessible_ids, true)) {
				$result[] = new BreadcrumbItemResource(null, '...', null);
				break;
			}
			$result[] = new BreadcrumbItemResource($ancestor->id, $ancestor->title, $ancestor->slug);
		}

		return array_reverse($result);
	}

	/**
	 * @param string[] $ancestor_ids
	 *
	 * @return string[]
	 */
	private function getAccessibleAncestorIds(array $ancestor_ids): array
	{
		$user = Auth::user();

		if ($user?->may_administrate === true) {
			return $ancestor_ids;
		}

		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$query = DB::table('base_albums')
			->whereIn('base_albums.id', $ancestor_ids)
			->select('base_albums.id');

		$this->album_query_policy->joinSubComputedAccessPermissions(
			$query, 'base_albums.id', 'left', '', false, $user
		);

		$query->where(fn (BaseBuilder $q) => $this->album_query_policy->appendAccessibilityConditions($q, $user, $unlocked_album_ids));

		return $query->pluck('id')->all();
	}
}
