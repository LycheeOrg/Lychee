<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Frame;

use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Facades\Auth;

/**
 * Titles of the photo's albums the viewer can browse to, for the Frame
 * album line (Feature 088, FR-088-06).
 *
 * Browsability is the rule of album search: albums that require their link
 * stay out, even when the viewer could open them by link.
 */
class ListBrowsableAlbumTitles
{
	public function __construct(
		private AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * @return array<int,string> titles sorted by title
	 */
	public function do(Photo $photo): array
	{
		$query = Album::query()
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereIn('albums.id', $photo->albums->pluck('id')->all());
		$this->album_query_policy->applyBrowsabilityFilter($query, Auth::user(), AlbumPolicy::getUnlockedAlbumIDs());

		return $query
			->orderBy('base_albums.title_base')
			->orderBy('base_albums.title_index')
			->orderBy('base_albums.title')
			->pluck('base_albums.title')
			->all();
	}
}
