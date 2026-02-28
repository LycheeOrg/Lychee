<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\AlbumLayoutType;
use App\Enum\DefaultAlbumProtectionType;
use App\Enum\ImageOverlayType;
use App\Enum\PaginationMode;
use App\Enum\PhotoHighlightVisibilityType;
use App\Enum\PhotoThumbInfoType;
use App\Enum\SmallLargeType;
use App\Enum\ThumbAlbumSubtitleType;
use App\Enum\VisibilityType;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use LycheeVerify\Verify;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class InitConfig extends Data
{
	// ! This will make the error on http requests or front-end being displayed very visibly.
	public bool $is_debug_enabled;

	// NSFW settings
	public bool $are_nsfw_visible;
	public bool $is_nsfw_background_blurred;
	public string $nsfw_banner_override;
	public bool $is_nsfw_banner_backdrop_blurred;

	// Keybinding help popup
	public bool $show_keybinding_help_popup;

	// Image overlay settings
	public ImageOverlayType $image_overlay_type;
	public bool $can_rotate;
	public bool $can_autoplay;
	public bool $is_exif_disabled;
	public bool $is_favourite_enabled;
	public SmallLargeType $photo_previous_next_size;
	public bool $is_details_links_enabled;
	public bool $is_desktop_dock_full_transparency_enabled;
	public bool $is_mobile_dock_full_transparency_enabled;
	public bool $is_photo_details_always_open;

	// Thumbs configuration
	public VisibilityType $display_thumb_album_overlay;
	public VisibilityType $display_thumb_photo_overlay;
	public ThumbAlbumSubtitleType $album_subtitle_type;
	public AlbumDecorationType $album_decoration;
	public AlbumDecorationOrientation $album_decoration_orientation;
	#[LiteralTypeScriptType('1|2|3')]
	public int $number_albums_per_row_mobile;
	public PhotoThumbInfoType $photo_thumb_info;
	public bool $is_photo_thumb_tags_enabled;

	// Album view mode
	public AlbumLayoutType $album_layout;

	// Download configuration
	public bool $is_raw_download_enabled;
	public bool $is_thumb_download_enabled;
	public bool $is_thum2x_download_enabled;
	public bool $is_small_download_enabled;
	public bool $is_small2x_download_enabled;
	public bool $is_medium_download_enabled;
	public bool $is_medium2x_download_enabled;

	// Clockwork
	public ?string $clockwork_url;

	// Slideshow setting
	public int $slideshow_timeout;
	public bool $is_slideshow_enabled;

	// Timeline settings
	public bool $is_timeline_left_border_visible;

	// Site title & dropbox key if logged in as admin.
	public string $title;
	public string $dropbox_api_key;

	// Lychee SE is available.
	public bool $is_se_enabled;
	public bool $is_pro_enabled;
	// Lychee SE is not available, but preview is enabled.
	public bool $is_se_preview_enabled;
	// We hide the info about Lychee SE if the user is already a supporter
	// or if they asked to hide it (because we are nice :) ).
	public bool $is_se_info_hidden;
	public bool $is_se_expired;

	// Live Metrics settings
	public bool $is_live_metrics_enabled;

	public bool $is_basic_auth_enabled = true;
	public bool $is_webauthn_enabled = true;
	// User registration enabled
	public bool $is_registration_enabled;

	// Gesture settings
	public bool $is_scroll_to_navigate_photos_enabled;
	public bool $is_swipe_vertically_to_go_back_enabled;

	// Rating settings
	public bool $is_rating_show_avg_in_details_enabled;
	public bool $is_rating_show_avg_in_photo_view_enabled;
	public VisibilityType $rating_photo_view_mode;
	public bool $is_rating_show_avg_in_album_view_enabled;
	public VisibilityType $rating_album_view_mode;

	// Homepage
	public string $default_homepage;
	public bool $is_timeline_page_enabled = false;

	// Pagination settings
	public PaginationMode $photos_pagination_mode;
	public PaginationMode $albums_pagination_mode;
	public int $photos_per_page;
	public int $albums_per_page;
	public int $photos_infinite_scroll_threshold;
	public int $albums_infinite_scroll_threshold;

	// Album settings
	public DefaultAlbumProtectionType $default_album_protection;
	public PhotoHighlightVisibilityType $photos_star_visibility;

	public function __construct()
	{
		// Debug mode
		$this->is_debug_enabled = config('app.debug');

		// NSFW settings
		$this->are_nsfw_visible = request()->configs()->getValueAsBool('nsfw_visible');
		$this->is_nsfw_background_blurred = request()->configs()->getValueAsBool('nsfw_blur'); // blur the thumbnails
		$this->nsfw_banner_override = request()->configs()->getValueAsString('nsfw_banner_override'); // override the banner text.
		$this->is_nsfw_banner_backdrop_blurred = request()->configs()->getValueAsBool('nsfw_banner_blur_backdrop'); // blur the backdrop of the warning banner.

		// keybinding help popup
		$this->show_keybinding_help_popup = request()->configs()->getValueAsBool('show_keybinding_help_popup');

		// Image overlay settings
		$this->image_overlay_type = request()->configs()->getValueAsEnum('image_overlay_type', ImageOverlayType::class);
		$this->can_rotate = request()->configs()->getValueAsBool('editor_enabled');
		$this->can_autoplay = request()->configs()->getValueAsBool('autoplay_enabled');
		$this->is_exif_disabled = request()->configs()->getValueAsBool('exif_disabled_for_all');
		$this->is_favourite_enabled = request()->configs()->getValueAsBool('client_side_favourite_enabled');
		$this->photo_previous_next_size = request()->configs()->getValueAsEnum('photo_previous_next_size', SmallLargeType::class);
		$this->is_details_links_enabled = false;
		if (request()->configs()->getValueAsBool('details_links_enabled')) {
			$this->is_details_links_enabled = !Auth::guest() || request()->configs()->getValueAsBool('details_links_public');
		}
		$this->is_desktop_dock_full_transparency_enabled = request()->configs()->getValueAsBool('desktop_dock_full_transparency_enabled');
		$this->is_mobile_dock_full_transparency_enabled = request()->configs()->getValueAsBool('mobile_dock_full_transparency_enabled');
		$this->is_photo_details_always_open = request()->configs()->getValueAsBool('enable_photo_details_always_open');

		// Thumbs configuration
		$this->display_thumb_album_overlay = request()->configs()->getValueAsEnum('display_thumb_album_overlay', VisibilityType::class);
		$this->display_thumb_photo_overlay = request()->configs()->getValueAsEnum('display_thumb_photo_overlay', VisibilityType::class);
		$this->album_subtitle_type = request()->configs()->getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class);
		$this->album_decoration = request()->configs()->getValueAsEnum('album_decoration', AlbumDecorationType::class);
		$this->album_decoration_orientation = request()->configs()->getValueAsEnum('album_decoration_orientation', AlbumDecorationOrientation::class);
		$this->number_albums_per_row_mobile = request()->configs()->getValueAsInt('number_albums_per_row_mobile');
		$this->photo_thumb_info = request()->configs()->getValueAsEnum('photo_thumb_info', PhotoThumbInfoType::class);
		$this->is_photo_thumb_tags_enabled = request()->configs()->getValueAsBool('photo_thumb_tags_enabled');
		$this->album_layout = request()->configs()->getValueAsEnum('album_layout', AlbumLayoutType::class);

		// Download configuration
		$this->is_raw_download_enabled = request()->configs()->getValueAsBool('raw_download_enabled');
		$this->is_thumb_download_enabled = request()->configs()->getValueAsBool('disable_thumb_download') === false;
		$this->is_thum2x_download_enabled = request()->configs()->getValueAsBool('disable_thumb2x_download') === false;
		$this->is_small_download_enabled = request()->configs()->getValueAsBool('disable_small_download') === false;
		$this->is_small2x_download_enabled = request()->configs()->getValueAsBool('disable_small2x_download') === false;
		$this->is_medium_download_enabled = request()->configs()->getValueAsBool('disable_medium_download') === false;
		$this->is_medium2x_download_enabled = request()->configs()->getValueAsBool('disable_medium2x_download') === false;

		// Clockwork
		$this->has_clockwork_in_menu();

		// Slideshow settings
		$this->slideshow_timeout = request()->configs()->getValueAsInt('slideshow_timeout');
		$this->is_slideshow_enabled = request()->configs()->getValueAsBool('slideshow_enabled');

		// Timeline settings
		$this->is_timeline_left_border_visible = request()->configs()->getValueAsBool('timeline_left_border_enabled');

		// Site title & dropbox key if logged in as admin.
		// dd(request()->config());
		$this->title = request()->configs()->getValueAsString('site_title');
		$this->dropbox_api_key = Auth::user()?->may_administrate === true ? request()->configs()->getValueAsString('dropbox_key') : 'disabled';

		$this->is_basic_auth_enabled = AuthServiceProvider::isBasicAuthEnabled();
		$this->is_webauthn_enabled = AuthServiceProvider::isWebAuthnEnabled();
		// User registration enabled
		$this->is_registration_enabled = request()->configs()->getValueAsBool('user_registration_enabled');

		// Gesture settings
		$this->is_scroll_to_navigate_photos_enabled = request()->configs()->getValueAsBool('is_scroll_to_navigate_photos_enabled');
		$this->is_swipe_vertically_to_go_back_enabled = request()->configs()->getValueAsBool('is_swipe_vertically_to_go_back_enabled');

		// Rating settings
		$this->is_rating_show_avg_in_details_enabled = request()->configs()->getValueAsBool('rating_show_avg_in_details');
		$this->is_rating_show_avg_in_photo_view_enabled = request()->configs()->getValueAsBool('rating_show_avg_in_photo_view');
		$this->rating_photo_view_mode = request()->configs()->getValueAsEnum('rating_photo_view_mode', VisibilityType::class);
		$this->is_rating_show_avg_in_album_view_enabled = request()->configs()->getValueAsBool('rating_show_avg_in_album_view');
		$this->rating_album_view_mode = request()->configs()->getValueAsEnum('rating_album_view_mode', VisibilityType::class);

		// Homepage
		$this->default_homepage = request()->configs()->getValueAsString('home_page_default');
		$this->is_timeline_page_enabled = request()->configs()->getValueAsBool('timeline_page_enabled');

		// Pagination settings
		$this->photos_pagination_mode = request()->configs()->getValueAsEnum('photos_pagination_ui_mode', PaginationMode::class);
		$this->albums_pagination_mode = request()->configs()->getValueAsEnum('albums_pagination_ui_mode', PaginationMode::class);
		$this->photos_per_page = request()->configs()->getValueAsInt('photos_per_page');
		$this->albums_per_page = request()->configs()->getValueAsInt('albums_per_page');
		$this->photos_infinite_scroll_threshold = request()->configs()->getValueAsInt('photos_infinite_scroll_threshold');
		$this->albums_infinite_scroll_threshold = request()->configs()->getValueAsInt('albums_infinite_scroll_threshold');

		// Album settings
		$this->default_album_protection = request()->configs()->getValueAsEnum('default_album_protection', DefaultAlbumProtectionType::class);
		$this->photos_star_visibility = request()->configs()->getValueAsEnum('photos_star_visibility', PhotoHighlightVisibilityType::class);

		$this->set_supporter_properties();
	}

	/**
	 * For clockwork we need to check that it is enabled or that we are in debug mode.
	 * Furthermore we need to check if the web interface is enabled.
	 *
	 * @return void
	 */
	private function has_clockwork_in_menu(): void
	{
		// Defining clockwork URL
		$clock_work_enabled = config('clockwork.enable') === true || (config('app.debug') === true && config('clockwork.enable') === null);
		$clock_work_web = config('clockwork.web');

		$this->clockwork_url = match (true) {
			$clock_work_enabled && ($clock_work_web === true) => URL::asset('clockwork/app'),
			is_string($clock_work_web) => $clock_work_web . '/app',
			default => null,
		};
	}

	/**
	 * We set the properties related to Lychee SE.
	 *
	 * @return void
	 */
	private function set_supporter_properties()
	{
		$is_supporter = request()->verify()->is_supporter();
		$is_pro = request()->verify()->is_pro();

		// We enable Lychee SE if the user is a supporter.
		$verify = request()->verify();
		$this->is_se_enabled = $verify instanceof Verify && $verify->validate() && $is_supporter;
		$this->is_pro_enabled = $this->is_se_enabled && $is_pro;

		// We disable preview if we are already a supporter.
		$this->is_se_preview_enabled = !$is_supporter && request()->configs()->getValueAsBool('enable_se_preview');

		// We hide the info if we are already a supporter (or the user requests it).
		$this->is_se_info_hidden = $is_supporter || request()->configs()->getValueAsBool('disable_se_call_for_actions');

		$this->is_live_metrics_enabled = $this->is_se_enabled && request()->configs()->getValueAsBool('live_metrics_enabled');

		$this->is_se_expired = request()->configs()->getValueAsString('license_key') !== '' && !$this->is_se_enabled;
	}
}