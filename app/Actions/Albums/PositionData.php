<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SizeVariantType;
use App\Http\Resources\Collections\PositionDataResource;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;

class PositionData
{
	protected PhotoQueryPolicy $photo_query_policy;

	public function __construct(PhotoQueryPolicy $photo_query_policy)
	{
		$this->photo_query_policy = $photo_query_policy;
		// caching to avoid further request
		Configs::get();
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
				])
				->whereNotNull('latitude')
				->whereNotNull('longitude'),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_map')
		);

		return new PositionDataResource(null, $photo_query->get(), null);
	}
}
