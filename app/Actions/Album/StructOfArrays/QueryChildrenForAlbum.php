<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Enum\OrderSortingType;
use App\Http\Resources\V3\AlbumDataResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;

class QueryChildrenForAlbum
{
	public function __construct(
		private readonly BuildAlbumDataResource $build_album_data_resource,
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	public function do(Album $album, ?User $user): AlbumDataResource
	{
		$query = Album::query()->where('parent_id', '=', $album->id);
		// applyVisibilityFilter() already joins computed_access_permissions
		// internally (prepareModelQueryOrFail()) — must not join it again.
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		$sorting = $album->getEffectiveAlbumSorting();
		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';
		// Order by bucket_id first (mirrors queryBuckets() exactly,
		// "unknown" always last) so grouping these rows by bucket_id
		// reproduces the buckets endpoint's own row order; this is required, not
		// redundant with the effective-column order below, because under
		// title_bucket_mode=date_prefix the parsed-date bucket_id and the
		// title_base/title_index sort key are unrelated dimensions of the same
		// string, so same-bucket rows would not otherwise stay contiguous.
		$query->orderByRaw('(albums.bucket_id IS NULL) ASC')
			->orderBy('albums.bucket_id', $direction);
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		return $this->build_album_data_resource->do($query, $user);
	}
}
