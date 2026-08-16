<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\LandingAnimationPreset;
use App\Enum\LandingBackgroundModeType;
use App\Enum\LandingCtaPosition;
use App\Enum\LandingFeaturedItemsMode;
use App\Enum\LandingFeaturedItemType;
use App\Enum\LandingLayoutType;
use App\Enum\LandingLoginPosition;
use App\Enum\LandingTextPosition;
use App\Enum\ShiftType;
use App\Enum\ShiftX;
use App\Enum\ShiftY;
use App\Models\Album;
use App\Models\LandingFeaturedItem;
use App\Models\LandingLink;
use App\Models\Photo;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use LycheeVerify\Verify;
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
	public string $landing_logo;
	public string $landing_header_logo;
	public FooterConfig $footer;

	public LandingLayoutType $layout;
	public bool $intro_screen_enabled;
	public int $backdrop_opacity;
	public LandingTextPosition $hero_text_position;
	public string $hero_text_color;
	public int $hero_text_opacity;
	public LandingAnimationPreset $animation_preset;
	public bool $about_enabled;
	public string $about_text;
	public bool $featured_items_enabled;
	public LandingFeaturedItemsMode $featured_items_mode;
	/** @var LandingFeaturedContentResource[] */
	public array $featured_items;
	/** @var LandingLinkEmbedResource[] */
	public array $links;
	public string $cta_text;
	public LandingCtaPosition $cta_position;
	public ShiftType $cta_shift_type;
	public int $cta_shift_x;
	public ShiftX $cta_shift_x_direction;
	public int $cta_shift_y;
	public ShiftY $cta_shift_y_direction;
	public int $meridian_explore_offset;
	public int $meridian_contact_offset;
	public int $meridian_explore_line_position;
	public int $meridian_contact_line_position;
	public LandingLoginPosition $login_position;

	private const FALLBACK_IMAGE = 'dist/cat.webp';

	/** SE-only landing layouts. Non-SE requesters silently fall back to `classic`. */
	private const SE_LAYOUTS = [LandingLayoutType::PORTFOLIO, LandingLayoutType::MERIDIAN, LandingLayoutType::STUDIO];

	/** SE-only animation presets. Non-SE requesters silently fall back to `classic_fade`. */
	private const SE_ANIMATION_PRESETS = [LandingAnimationPreset::ZOOM_IN, LandingAnimationPreset::PARALLAX_SCROLL, LandingAnimationPreset::SLIDE_REVEAL];

	public function __construct()
	{
		$this->footer = new FooterConfig();
		$this->landing_page_enable = request()->configs()->getValueAsBool('landing_page_enable');

		// Resolve dynamic backgrounds based on mode configs
		$landscape_mode = request()->configs()->getValueAsEnum('landing_background_landscape_mode', LandingBackgroundModeType::class);
		$landscape_value = request()->configs()->getValueAsString('landing_background_landscape');
		$this->landing_background_landscape = $this->resolveBackgroundUrl($landscape_mode, $landscape_value);

		$portrait_mode = request()->configs()->getValueAsEnum('landing_background_portrait_mode', LandingBackgroundModeType::class);
		$portrait_value = request()->configs()->getValueAsString('landing_background_portrait');
		$this->landing_background_portrait = $this->resolveBackgroundUrl($portrait_mode, $portrait_value);

		$this->landing_subtitle = request()->configs()->getValueAsString('landing_subtitle');
		$this->landing_title = request()->configs()->getValueAsString('landing_title');
		$this->site_owner = request()->configs()->getValueAsString('site_owner');
		$this->site_title = request()->configs()->getValueAsString('site_title');
		$this->landing_logo = request()->configs()->getValueAsString('landing_logo');
		$this->landing_header_logo = request()->configs()->getValueAsString('landing_header_logo');

		$is_se_enabled = $this->isSeEnabled();

		$stored_layout = request()->configs()->getValueAsEnum('landing_layout', LandingLayoutType::class) ?? LandingLayoutType::CLASSIC;
		$this->layout = ($is_se_enabled || !in_array($stored_layout, self::SE_LAYOUTS, true)) ? $stored_layout : LandingLayoutType::CLASSIC;

		$stored_animation_preset = request()->configs()->getValueAsEnum('landing_animation_preset', LandingAnimationPreset::class) ?? LandingAnimationPreset::CLASSIC_FADE;
		$this->animation_preset = ($is_se_enabled || !in_array($stored_animation_preset, self::SE_ANIMATION_PRESETS, true))
			? $stored_animation_preset
			: LandingAnimationPreset::CLASSIC_FADE;

		$this->intro_screen_enabled = request()->configs()->getValueAsBool('landing_intro_screen_enabled');
		$this->backdrop_opacity = request()->configs()->getValueAsInt('landing_backdrop_opacity');
		$this->hero_text_position = request()->configs()->getValueAsEnum('landing_hero_text_position', LandingTextPosition::class) ?? LandingTextPosition::CENTER;
		$this->hero_text_color = request()->configs()->getValueAsString('landing_hero_text_color');
		$this->hero_text_opacity = request()->configs()->getValueAsInt('landing_hero_text_opacity');

		$this->about_enabled = request()->configs()->getValueAsBool('landing_about_enabled');
		$this->about_text = request()->configs()->getValueAsString('landing_about_text');

		$this->cta_text = request()->configs()->getValueAsString('landing_cta_text');
		$this->cta_position = request()->configs()->getValueAsEnum('landing_cta_position', LandingCtaPosition::class) ?? LandingCtaPosition::BOTTOM;
		$this->cta_shift_type = request()->configs()->getValueAsEnum('landing_cta_shift_type', ShiftType::class) ?? ShiftType::RELATIVE;
		$this->cta_shift_x = request()->configs()->getValueAsInt('landing_cta_shift_x');
		$this->cta_shift_x_direction = request()->configs()->getValueAsEnum('landing_cta_shift_x_direction', ShiftX::class) ?? ShiftX::RIGHT;
		$this->cta_shift_y = request()->configs()->getValueAsInt('landing_cta_shift_y');
		$this->cta_shift_y_direction = request()->configs()->getValueAsEnum('landing_cta_shift_y_direction', ShiftY::class) ?? ShiftY::UP;
		$this->meridian_explore_offset = request()->configs()->getValueAsInt('landing_meridian_explore_offset');
		$this->meridian_contact_offset = request()->configs()->getValueAsInt('landing_meridian_contact_offset');
		$this->meridian_explore_line_position = request()->configs()->getValueAsInt('landing_meridian_explore_line_position');
		$this->meridian_contact_line_position = request()->configs()->getValueAsInt('landing_meridian_contact_line_position');
		$this->login_position = request()->configs()->getValueAsEnum('landing_login_position', LandingLoginPosition::class) ?? LandingLoginPosition::SIDE;

		$this->links = LandingLink::query()->enabled()->orderBy('sort_order')->get()
			->map(fn (LandingLink $landing_link) => new LandingLinkEmbedResource($landing_link))
			->all();

		// Featured content is SE-gated in its entirety (Goal 6 / FR-054-09).
		$this->featured_items_enabled = $is_se_enabled && request()->configs()->getValueAsBool('landing_featured_items_enabled');

		$stored_featured_items_mode = request()->configs()->getValueAsEnum('landing_featured_items_mode', LandingFeaturedItemsMode::class) ?? LandingFeaturedItemsMode::AUTOMATIC;
		$this->featured_items_mode = ($is_se_enabled || $stored_featured_items_mode !== LandingFeaturedItemsMode::MANUAL)
			? $stored_featured_items_mode
			: LandingFeaturedItemsMode::AUTOMATIC;

		$this->featured_items = $this->featured_items_enabled ? $this->resolveFeaturedItems($this->featured_items_mode) : [];
	}

	/**
	 * Mirrors `InitConfig::set_supporter_properties()`'s `is_se_enabled` derivation.
	 */
	private function isSeEnabled(): bool
	{
		$verify = request()->verify();

		return $verify instanceof Verify && $verify->validate() && $verify->is_supporter();
	}

	/**
	 * @return LandingFeaturedContentResource[]
	 */
	private function resolveFeaturedItems(LandingFeaturedItemsMode $mode): array
	{
		try {
			return match ($mode) {
				LandingFeaturedItemsMode::AUTOMATIC => $this->resolveAutomaticFeaturedItems(),
				LandingFeaturedItemsMode::MANUAL => $this->resolveManualFeaturedItems(),
			};
		} catch (\Throwable $e) {
			\Log::notice('Landing featured-items resolution failed', [
				'mode' => $mode->value,
				'error' => $e->getMessage(),
			]);

			return [];
		}
	}

	/**
	 * Most-recently-published public albums, same query shape as
	 * `resolveLatestAlbumCover()` (Feature 025).
	 *
	 * @return LandingFeaturedContentResource[]
	 */
	private function resolveAutomaticFeaturedItems(): array
	{
		$count = request()->configs()->getValueAsInt('landing_featured_items_count');

		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$query = Album::query()->with(['cover.size_variants', 'min_privilege_cover.size_variants']);
		$query = $album_query_policy->applyVisibilityFilter($query, null);

		$albums = $query
			->orderBy('base_albums.published_at', 'DESC')
			->orderBy('base_albums.created_at', 'DESC')
			->orderBy('albums.id', 'DESC')
			->limit($count)
			->get();

		return $albums->map(fn (Album $album) => new LandingFeaturedContentResource($album))->all();
	}

	/**
	 * Admin-curated photos/albums, resolved by direct lookup on `item_id`
	 * without a visibility-policy check (admin-trusted, mirrors
	 * `resolvePhotoById()`'s precedent). Missing/deleted items are skipped.
	 *
	 * @return LandingFeaturedContentResource[]
	 */
	private function resolveManualFeaturedItems(): array
	{
		$items = LandingFeaturedItem::query()->enabled()->orderBy('sort_order')->get();

		$resolved = [];
		foreach ($items as $item) {
			$model = match ($item->item_type) {
				LandingFeaturedItemType::PHOTO => Photo::query()->with('size_variants')->find($item->item_id),
				LandingFeaturedItemType::ALBUM => Album::query()->with(['cover.size_variants', 'min_privilege_cover.size_variants'])->find($item->item_id),
			};

			if ($model !== null) {
				$resolved[] = new LandingFeaturedContentResource($model);
			}
		}

		return $resolved;
	}

	/**
	 * Resolves background URL based on mode and value.
	 * Always returns a valid URL string - never throws exceptions.
	 *
	 * @param LandingBackgroundModeType|null $mode  The resolution mode enum
	 * @param string                         $value The value to use (URL, photo ID, or album ID depending on mode)
	 *
	 * @return string The resolved URL or fallback image
	 */
	private function resolveBackgroundUrl(?LandingBackgroundModeType $mode, string $value): string
	{
		try {
			if ($mode === null) {
				return self::FALLBACK_IMAGE;
			}

			return match ($mode) {
				LandingBackgroundModeType::STATIC => $this->resolveStatic($value),
				LandingBackgroundModeType::PHOTO_ID => $this->resolvePhotoById($value),
				LandingBackgroundModeType::RANDOM => $this->resolveRandomPhoto(),
				LandingBackgroundModeType::LATEST_ALBUM_COVER => $this->resolveLatestAlbumCover(),
				LandingBackgroundModeType::RANDOM_FROM_ALBUM => $this->resolveRandomFromAlbum($value),
			};
		} catch (\Throwable $e) {
			// Graceful fallback - log error but don't break landing page
			\Log::notice('Landing background resolution failed', [
				'mode' => $mode?->value,
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
			->orderBy('base_albums.published_at', 'DESC')
			->orderBy('base_albums.created_at', 'DESC')
			->orderBy('albums.id', 'DESC')
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
			->whereHas('albums', static function ($query) use ($album): void {
				$query->whereKey($album->id);
			})
			->inRandomOrder()
			->limit(1)
			->first();

		if ($photo === null) {
			return self::FALLBACK_IMAGE;
		}

		return $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url ?? self::FALLBACK_IMAGE;
	}
}
