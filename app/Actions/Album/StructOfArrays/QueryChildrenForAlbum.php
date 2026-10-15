<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Http\Resources\V3\AlbumDataResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\AlbumBucketComputer;

class QueryChildrenForAlbum
{
	public function __construct(
		private readonly BuildAlbumDataResource $build_album_data_resource,
		private readonly AlbumQueryPolicy $album_query_policy,
		private readonly AlbumBucketComputer $album_bucket_computer,
	) {
	}

	public function do(Album $album, ?User $user): AlbumDataResource
	{
		$query = Album::query()->where('parent_id', '=', $album->id);
		// applyVisibilityFilter() already joins computed_access_permissions
		// internally (prepareModelQueryOrFail()) — must not join it again.
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		$sorting = $album->getEffectiveAlbumSorting();
		$scheme = $this->album_bucket_computer->scheme($sorting->column, $album->album_timeline);
		// Order by bucket id first (`unknown` last), mirroring the `buckets`
		// tier, so grouping these rows by id reproduces it: under
		// title_bucket_mode=date_prefix the title date and the title sort key
		// are unrelated, so same-bucket rows would not otherwise stay
		// contiguous.
		$scheme->orderRows($query, $scheme->storedIdSql('albums.bucket_key'), $sorting->order);
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		return $this->build_album_data_resource->do($query, $user, $scheme);
	}
}
