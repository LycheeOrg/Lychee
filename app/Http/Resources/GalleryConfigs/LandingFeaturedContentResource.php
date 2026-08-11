<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\LandingFeaturedItemType;
use App\Models\Album;
use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Unified photo/album projection for the landing page featured-content
 * section. Used by both automatic mode (recent public albums) and manual
 * mode (admin-curated photos/albums).
 */
#[TypeScript()]
class LandingFeaturedContentResource extends Data
{
	public LandingFeaturedItemType $item_type;
	public string $id;
	public string $title;
	public string $thumb_url;
	public string $url;
	public ?int $num_photos;

	private const FALLBACK_IMAGE = 'dist/cat.webp';

	public function __construct(Photo|Album $item)
	{
		if ($item instanceof Photo) {
			$this->item_type = LandingFeaturedItemType::PHOTO;
			$this->id = $item->id;
			$this->title = $item->title;
			$this->thumb_url = $item->size_variants->getThumb()?->url ?? $item->size_variants->getMedium()?->url ?? self::FALLBACK_IMAGE;
			$album_id = $item->albums()->first()?->id;
			$this->url = $album_id !== null
				? route('gallery', ['albumId' => $album_id, 'photoId' => $item->id])
				: route('gallery');
			$this->num_photos = null;

			return;
		}

		$this->item_type = LandingFeaturedItemType::ALBUM;
		$this->id = $item->id;
		$this->title = $item->title;
		$cover_id = $item->cover_id ?? $item->auto_cover_id_least_privilege;
		$cover_photo = $cover_id !== null ? Photo::query()->with('size_variants')->find($cover_id) : null;
		$this->thumb_url = $cover_photo?->size_variants->getThumb()?->url ?? $cover_photo?->size_variants->getMedium()?->url ?? self::FALLBACK_IMAGE;
		$this->url = route('gallery', ['albumId' => $item->id]);
		$this->num_photos = $item->num_photos;
	}
}
