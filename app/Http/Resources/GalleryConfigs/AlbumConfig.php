<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingType;
use App\Enum\DateOrderingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Resources\Traits\HasTimelineData;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AlbumConfig extends Data
{
	use HasTimelineData;

	public bool $is_base_album;
	public bool $is_model_album;
	public bool $is_password_protected;
	public bool $is_map_accessible;
	public bool $is_mod_frame_enabled;
	public bool $is_search_accessible;
	public bool $is_nsfw_warning_visible;
	public bool $is_breadcrumb_enabled;
	public AspectRatioCSSType $album_thumb_css_aspect_ratio;
	public string $date_format_album_thumb;
	public DateOrderingType $thumb_min_max_order;
	public PhotoLayoutType $photo_layout;
	public bool $is_album_timeline_enabled = false;
	public bool $is_photo_timeline_enabled = false;
	/** Feature 071: whether the date scrubber rail may be shown (per-album override, else global default). */
	public bool $is_date_scrubber_enabled;
	/** Feature 071: the field photo tiles are dated by on the rail (`taken_at`, `created_at`, `title`), or null when the photo ordering is not date-based. */
	public ?string $photo_date_scrubber_field;
	/** Feature 071: the field sub-album tiles are dated by on the rail (`created_at`, `min_taken_at`, `max_taken_at`, `title`), or null. */
	public ?string $album_date_scrubber_field;
	/** Feature 071: PHP `date()` format for the rail's day labels. */
	public string $date_scrubber_label_format;

	public function __construct(AbstractAlbum $album)
	{
		$config_manager = app(ConfigManager::class);
		$is_accessible = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]);
		$public_perm = $album->public_permissions();

		$this->is_base_album = $album instanceof BaseAlbum;
		$this->is_model_album = $album instanceof Album;
		$this->is_breadcrumb_enabled = $this->is_model_album && $config_manager->getValueAsBool('breadcrumb_enabled');
		$this->is_password_protected = !$is_accessible && $public_perm?->password !== null;
		$this->is_nsfw_warning_visible =
			$album instanceof BaseAlbum &&
			$album->is_nsfw &&
			(Auth::check() ? $config_manager->getValueAsBool('nsfw_warning_admin') : $config_manager->getValueAsBool('nsfw_warning'));

		$this->setIsMapAccessible();
		$this->setIsSearchAccessible($this->is_base_album);
		$this->is_mod_frame_enabled = $config_manager->getValueAsBool('mod_frame_enabled') && $album->get_photos()->count() > 0;
		if ($album instanceof Album && $album->album_thumb_aspect_ratio !== null) {
			$this->album_thumb_css_aspect_ratio = $album->album_thumb_aspect_ratio->css();
		} else {
			$this->album_thumb_css_aspect_ratio = $config_manager->getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
		}

		$this->date_format_album_thumb = $config_manager->getValueAsString('date_format_album_thumb');
		$this->thumb_min_max_order = $config_manager->getValueAsEnum('thumb_min_max_order', DateOrderingType::class);

		$this->photo_layout = (($album instanceof BaseAlbum) ? $album->photo_layout : null) ?? $config_manager->getValueAsEnum('layout', PhotoLayoutType::class);

		// Set default values.
		$this->is_photo_timeline_enabled = $config_manager->getValueAsBool('timeline_photos_enabled');
		$this->is_album_timeline_enabled = $config_manager->getValueAsBool('timeline_albums_enabled');

		if ($album instanceof Album) {
			$this->is_album_timeline_enabled = $album->album_timeline !== null || $this->is_album_timeline_enabled;
			$this->is_album_timeline_enabled = $album->album_timeline !== TimelineAlbumGranularity::DISABLED && $this->is_album_timeline_enabled;
		}

		if ($album instanceof BaseAlbum) {
			$this->is_photo_timeline_enabled = $album->photo_timeline !== null || $this->is_photo_timeline_enabled;
			$this->is_photo_timeline_enabled = $album->photo_timeline !== TimelinePhotoGranularity::DISABLED && $this->is_photo_timeline_enabled;
		}

		$this->is_date_scrubber_enabled = self::resolveDateScrubberEnabled($album, $config_manager);
		$this->photo_date_scrubber_field = self::resolvePhotoDateScrubberField($album, $config_manager);
		$this->album_date_scrubber_field = self::resolveAlbumDateScrubberField($album, $config_manager);
		$this->date_scrubber_label_format = $config_manager->getValueAsString('timeline_photo_date_format_day');

		// Masking to require login for timeline or allow it to be public.
		$this->is_photo_timeline_enabled = $this->is_photo_timeline_enabled && ($config_manager->getValueAsBool('timeline_photos_public') || Auth::check());
		$this->is_album_timeline_enabled = $this->is_album_timeline_enabled && ($config_manager->getValueAsBool('timeline_albums_public') || Auth::check());
	}

	private static function resolveDateScrubberEnabled(AbstractAlbum $album, ConfigManager $config_manager): bool
	{
		$override = $album instanceof BaseAlbum ? $album->is_date_scrubber_enabled : null;

		return $override ?? $config_manager->getValueAsBool('album_date_scrubber_enabled');
	}

	private static function resolvePhotoDateScrubberField(AbstractAlbum $album, ConfigManager $config_manager): ?string
	{
		$sorting = $album instanceof BaseAlbum ? $album->getEffectivePhotoSorting() : PhotoSortingCriterion::createDefault();
		$title_mode = $config_manager->getValueAsEnum('photo_title_bucket_mode', TitleBucketMode::class);

		return self::dateScrubberField($sorting->column, [ColumnSortingType::CREATED_AT, ColumnSortingType::TAKEN_AT], $title_mode);
	}

	private static function resolveAlbumDateScrubberField(AbstractAlbum $album, ConfigManager $config_manager): ?string
	{
		if (!$album instanceof Album) {
			return null;
		}
		$title_mode = $config_manager->getValueAsEnum('title_bucket_mode', TitleBucketMode::class);

		return self::dateScrubberField(
			$album->getEffectiveAlbumSorting()->column,
			[ColumnSortingType::CREATED_AT, ColumnSortingType::MIN_TAKEN_AT, ColumnSortingType::MAX_TAKEN_AT],
			$title_mode,
		);
	}

	/**
	 * The field tiles are dated by when `$column` orders them by date: the
	 * column itself for a date column, `title` for a title sort whose
	 * buckets are parsed from a leading date (same default as the bucket
	 * computers), otherwise null.
	 *
	 * @param ColumnSortingType[] $date_columns
	 */
	private static function dateScrubberField(ColumnSortingType $column, array $date_columns, ?TitleBucketMode $title_mode): ?string
	{
		if (in_array($column, $date_columns, true)) {
			return $column->value;
		}
		$is_date_prefixed_title = $column === ColumnSortingType::TITLE && ($title_mode ?? TitleBucketMode::DATE_PREFIX) === TitleBucketMode::DATE_PREFIX;

		return $is_date_prefixed_title ? ColumnSortingType::TITLE->value : null;
	}

	public function setIsMapAccessible(): void
	{
		$config_manager = app(ConfigManager::class);
		$map_display = $config_manager->getValueAsBool('map_display');
		$public_display = Auth::check() || $config_manager->getValueAsBool('map_display_public');
		$this->is_map_accessible = $map_display && $public_display;
	}

	public function setIsSearchAccessible(bool $is_base_album): void
	{
		$this->is_search_accessible = (Auth::check() || app(ConfigManager::class)->getValueAsBool('search_public')) && $is_base_album;
	}
}