<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\DTO\AlbumSortingCriterion;
use App\Http\Resources\V3\AlbumDataResource;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\AlbumRepository;
use App\Repositories\ConfigManager;

class QueryChildrenForTag
{
	public function __construct(
		private readonly BuildAlbumDataResource $build_album_data_resource,
		private readonly AlbumQueryPolicy $album_query_policy,
		private readonly AlbumRepository $album_repository,
		private readonly ConfigManager $config_manager,
	) {
	}

	public function do(TagAlbum $album, ?User $user): AlbumDataResource
	{
		if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
			return $this->build_album_data_resource->empty($user);
		}

		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$tag_ids = $album->tags->pluck('id')->all();
		$query = $this->album_repository->queryMatchingAlbumsForTag($tag_ids, $album->is_and, $unlocked_album_ids);
		// queryMatchingAlbumsForTag()/applyBrowsabilityFilter() do not join
		// computed_access_permissions on the outer query — add it here.
		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', '', false, $user);
		// No parent-governed bucket_id concept applies to a dynamically-matched,
		// disparately-parented result set (tier 1 excludes these types entirely) -
		// order by the same instance-wide default v2's paginated listing already
		// uses for this type.
		$default_sorting = AlbumSortingCriterion::createDefault();
		(new SortingDecorator($query))->orderBy($default_sorting->column, $default_sorting->order)->applyOrdering();

		return $this->build_album_data_resource->do($query, $user);
	}
}
