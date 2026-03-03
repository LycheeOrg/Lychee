<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingPageResource extends Data
{
	public bool $landing_page_enable;
	public string $landing_background_landscape;
	public string $landing_background_portrait;
	public string $landing_subtitle;
	public string $landing_title;
	public string $site_owner;
	public string $site_title;
	public FooterConfig $footer;

	private const FALLBACK_IMAGE = 'dist/cat.webp';

	public function __construct()
	{
		$this->footer = new FooterConfig();
		$this->landing_page_enable = request()->configs()->getValueAsBool('landing_page_enable');

		// Resolve dynamic backgrounds based on mode configs
		$landscape_mode = request()->configs()->getValueAsString('landing_background_landscape_mode');
		$landscape_value = request()->configs()->getValueAsString('landing_background_landscape');
		$this->landing_background_landscape = $this->resolveBackgroundUrl($landscape_mode, $landscape_value);

		$portrait_mode = request()->configs()->getValueAsString('landing_background_portrait_mode');
		$portrait_value = request()->configs()->getValueAsString('landing_background_portrait');
		$this->landing_background_portrait = $this->resolveBackgroundUrl($portrait_mode, $portrait_value);

		$this->landing_subtitle = request()->configs()->getValueAsString('landing_subtitle');
		$this->landing_title = request()->configs()->getValueAsString('landing_title');
		$this->site_owner = request()->configs()->getValueAsString('site_owner');
		$this->site_title = request()->configs()->getValueAsString('site_title');
	}

	/**
	 * Resolves background URL based on mode and value.
	 * Always returns a valid URL string - never throws exceptions.
	 *
	 * @param string $mode  The resolution mode (static|photo_id|random|latest_album_cover|random_from_album)
	 * @param string $value The value to use (URL, photo ID, or album ID depending on mode)
	 *
	 * @return string The resolved URL or fallback image
	 */
	private function resolveBackgroundUrl(string $mode, string $value): string
	{
		try {
			return match ($mode) {
				'static' => $this->resolveStatic($value),
				'photo_id' => $this->resolvePhotoById($value),
				'random' => $this->resolveRandomPhoto(),
				'latest_album_cover' => $this->resolveLatestAlbumCover(),
				'random_from_album' => $this->resolveRandomFromAlbum($value),
				default => self::FALLBACK_IMAGE,
			};
		} catch (\Throwable $e) {
			// Graceful fallback - log error but don't break landing page
			\Log::notice('Landing background resolution failed', [
				'mode' => $mode,
				'value' => $value,
				'error' => $e->getMessage(),
			]);

			return self::FALLBACK_IMAGE;
		}
	}

	/**
	 * Resolves static URL mode.
	 *
	 * @param string $value The URL value
	 *
	 * @return string The URL or fallback
	 */
	private function resolveStatic(string $value): string
	{
		return $value !== '' ? $value : self::FALLBACK_IMAGE;
	}

	/**
	 * Resolves photo by ID mode (no public access check).
	 *
	 * @param string $value The photo ID
	 *
	 * @return string The photo URL or fallback
	 */
	private function resolvePhotoById(string $value): string
	{
		$photo = Photo::query()->with(['size_variants'])->find($value);

		if ($photo === null) {
			return self::FALLBACK_IMAGE;
		}

		return $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url ?? self::FALLBACK_IMAGE;
	}

	/**
	 * Resolves random public photo mode.
	 *
	 * @return string The photo URL or fallback
	 */
	private function resolveRandomPhoto(): string
	{
		$photo_query_policy = resolve(PhotoQueryPolicy::class);
		$query = Photo::query()->with(['size_variants']);

		// Apply public access filter (user=null, no unlocked albums)
		$query = $photo_query_policy->applySearchabilityFilter($query, null, []);

		$photo = $query->inRandomOrder()->limit(1)->first();

		if ($photo === null) {
			return self::FALLBACK_IMAGE;
		}

		return $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url ?? self::FALLBACK_IMAGE;
	}

	/**
	 * Resolves latest album cover mode.
	 *
	 * @return string The photo URL or fallback
	 */
	private function resolveLatestAlbumCover(): string
	{
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$query = Album::query()->with(['cover.size_variants', 'min_privilege_cover.size_variants']);

		// Apply public visibility filter
		$query = $album_query_policy->applyVisibilityFilter($query, null);

		$album = $query
			->orderBy('published_at', 'DESC')
			->orderBy('created_at', 'DESC')
			->orderBy('id', 'DESC')
			->limit(1)
			->first();

		if ($album === null) {
			return self::FALLBACK_IMAGE;
		}

		// Try explicit cover first, then auto cover
		$cover_id = $album->cover_id ?? $album->auto_cover_id_least_privilege;

		if ($cover_id === null) {
			return self::FALLBACK_IMAGE;
		}

		$photo = Photo::query()->with(['size_variants'])->find($cover_id);

		if ($photo === null) {
			return self::FALLBACK_IMAGE;
		}

		return $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url ?? self::FALLBACK_IMAGE;
	}

	/**
	 * Resolves random photo from specified album mode.
	 *
	 * @param string $value The album ID
	 *
	 * @return string The photo URL or fallback
	 */
	private function resolveRandomFromAlbum(string $value): string
	{
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$query = Album::query();

		// Verify album exists and is public
		$query = $album_query_policy->applyVisibilityFilter($query, null);
		$album = $query->find($value);

		if ($album === null) {
			return self::FALLBACK_IMAGE;
		}

		// Get random photo from album
		$photo = Photo::query()
			->with(['size_variants'])
			->where('album_id', '=', $album->id)
			->inRandomOrder()
			->limit(1)
			->first();

		if ($photo === null) {
			return self::FALLBACK_IMAGE;
		}

		return $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url ?? self::FALLBACK_IMAGE;
	}
}
