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
use App\Models\PersonAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\AlbumRepository;
use App\Repositories\ConfigManager;

class QueryChildrenForPerson
{
	public function __construct(
		private readonly BuildAlbumDataResource $build_album_data_resource,
		private readonly AlbumQueryPolicy $album_query_policy,
		private readonly AlbumRepository $album_repository,
		private readonly ConfigManager $config_manager,
	) {
	}

	public function do(PersonAlbum $album, ?User $user): AlbumDataResource
	{
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return $this->build_album_data_resource->empty($user);
		}

		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$query = $this->album_repository->queryMatchingAlbumsForPerson($album, $unlocked_album_ids);
		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', '', false, $user);
		$default_sorting = AlbumSortingCriterion::createDefault();
		(new SortingDecorator($query))->orderBy($default_sorting->column, $default_sorting->order)->applyOrdering();

		return $this->build_album_data_resource->do($query, $user);
	}
}
