<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SizeVariantType;
use App\Http\Resources\Collections\PositionDataResource;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Auth;

class PositionData
{
	public function __construct(
		protected PhotoQueryPolicy $photo_query_policy,
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @return PositionDataResource
	 *
	 * @throws InternalLycheeException
	 */
	public function do(): PositionDataResource
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$photo_query = $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()
				->with([
					'statistics',
					'size_variants' => function ($r): void {
						// The web GUI only uses the small and thumb size
						// variants to show photos on a map; so we can save
						// hydrating the larger size variants
						// this really helps, if you want to show thousands
						// of photos on a map, as there are up to 7 size
						// variants per photo
						$r->whereBetween('type', [SizeVariantType::SMALL2X, SizeVariantType::THUMB]);
					},
					'palette',
					'tags',
					'rating',
				])
				->whereNotNull('latitude')
				->whereNotNull('longitude'),
			user: $user,
			unlocked_album_ids: $unlocked_album_ids,
			origin: null,
			include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_map')
		);

		return new PositionDataResource(
			album_id: null,
			title: null,
			photos: $photo_query->get(),
			track_url: null,
			should_downgrade: !$this->config_manager->getValueAsBool('grants_full_photo_access'),
		);
	}
}
