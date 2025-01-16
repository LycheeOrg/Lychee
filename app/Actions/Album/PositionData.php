<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Http\Resources\Collections\PositionDataResource;
use App\Models\Album;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionData extends Action
{
	public function get(AbstractAlbum $album, bool $includeSubAlbums = false): PositionDataResource
	{
		$photoRelation = ($album instanceof Album && $includeSubAlbums) ?
			$album->all_photos() :
			$album->photos();

		// @phpstan-ignore-next-line
		$photoRelation
			->with([
				'album' => function (BelongsTo $b) {
					// The album is required for photos to properly
					// determine access and visibility rights; but we
					// don't need to determine the cover and thumbnail for
					// each album
					$b->without(['cover', 'thumb']);
				},
				'size_variants' => function (HasMany $r) {
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
			->whereNotNull('longitude');

		return new PositionDataResource($album->id, $album->title, $photoRelation->get(), $album instanceof Album ? $album->track_url : null);
	}
}
