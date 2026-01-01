<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Shop;

use App\Http\Requests\Catalog\GetCatalogRequest;
use App\Http\Resources\Shop\CatalogResource;
use App\Models\Album;
use App\Models\Purchasable;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Routing\Controller;

class CatalogController extends Controller
{
	public function __construct(
		private AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * Get the catalog of purchasable items for an album.
	 *
	 * @param GetCatalogRequest $request The request
	 *
	 * @return CatalogResource
	 */
	public function getAlbumCatalog(GetCatalogRequest $request): CatalogResource
	{
		$album = $request->album();

		// Get album-level purchasables
		$album_purchasable = $album->purchasable()->where('is_active', true)->first();

		// Query to get photos with purchasable options
		$photo_purchasables = Purchasable::query()
			->where('album_id', $album->id)
			->whereNotNull('photo_id')
			->where('is_active', true)
			->get();

		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$children_purchasables = Purchasable::query()
			->whereNull('photo_id')
			->whereIn('album_id', $this->album_query_policy->applyBrowsabilityFilter(
				query: Album::query()->where('parent_id', $album->id)->select('id'),
				user: $request->user(),
				unlocked_album_ids: $unlocked_album_ids,
				origin_left: $album->_lft,
				origin_right: $album->_rgt))
			->where('is_active', true)
			->get();

		return new CatalogResource(
			album_purchasable: $album_purchasable,
			children_purchasables: $children_purchasables,
			photo_purchasables: $photo_purchasables,
		);
	}
}
