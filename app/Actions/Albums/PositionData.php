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
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
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
		$photoQuery = $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()
				->with([
					'album' => function ($b) {
						// The album is required for photos to properly
						// determine access and visibility rights; but we
						// don't need to determine the cover and thumbnail for
						// each album
						$b->without(['cover', 'thumb']);
					},
					'size_variants' => function ($r) {
						// The web GUI only uses the small and thumb size
						// variants to show photos on a map; so we can save
						// hydrating the larger size variants
						// this really helps, if you want to show thousands
						// of photos on a map, as there are up to 7 size
						// variants per photo
						$r->whereBetween('type', [SizeVariantType::SMALL2X, SizeVariantType::THUMB]);
					},
					'size_variants.sym_links',
				])
				->whereNotNull('latitude')
				->whereNotNull('longitude'),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_map')
		);

		return new PositionDataResource(null, null, $photoQuery->get(), null);
	}
}
