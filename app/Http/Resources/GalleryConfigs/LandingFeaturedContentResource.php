<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\LandingFeaturedItemType;
use App\Models\Album;
use App\Models\Extensions\SizeVariants;
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
	public ?string $thumb_url_2x;
	public ?int $width;
	public ?int $height;
	public string $url;
	public ?int $num_photos;

	private const FALLBACK_IMAGE = 'dist/cat.webp';

	public function __construct(Photo|Album $item)
	{
		if ($item instanceof Photo) {
			$this->item_type = LandingFeaturedItemType::PHOTO;
			$this->id = $item->id;
			$this->title = $item->title;
			$this->applyThumb($item->size_variants);
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
		$this->applyThumb($cover_photo?->size_variants);
		$this->url = route('gallery', ['albumId' => $item->id]);
		$this->num_photos = $item->num_photos;
	}

	/**
	 * Small preserves the photo's natural aspect ratio (thumb is always square-cropped to
	 * 200x200/400x400 - see SizeVariantDefaultFactory), which the featured section's masonry
	 * layout needs in order to size tiles by their real proportions. Small2x is offered
	 * alongside it for a `srcset` 2x candidate, matching the main gallery grid's own thumbnails
	 * (PhotoThumb.vue).
	 */
	private function applyThumb(?SizeVariants $size_variants): void
	{
		$variant = $size_variants?->getSmall() ?? $size_variants?->getThumb();
		$this->thumb_url = $variant?->url ?? self::FALLBACK_IMAGE;
		$this->thumb_url_2x = $size_variants?->getSmall2x()?->url;
		$this->width = $variant?->width;
		$this->height = $variant?->height;
	}
}
