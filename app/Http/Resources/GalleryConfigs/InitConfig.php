<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\ImageOverlayType;
use App\Enum\PhotoThumbInfoType;
use App\Enum\SmallLargeType;
use App\Enum\ThumbAlbumSubtitleType;
use App\Enum\ThumbOverlayVisibilityType;
use App\Models\Configs;
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

	// Thumbs configuration
	public ThumbOverlayVisibilityType $display_thumb_album_overlay;
	public ThumbOverlayVisibilityType $display_thumb_photo_overlay;
	public ThumbAlbumSubtitleType $album_subtitle_type;
	public AlbumDecorationType $album_decoration;
	public AlbumDecorationOrientation $album_decoration_orientation;
	#[LiteralTypeScriptType('1|2|3')]
	public int $number_albums_per_row_mobile;
	public PhotoThumbInfoType $photo_thumb_info;

	// Download configuration
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
	// Lychee SE is not available, but preview is enabled.
	public bool $is_se_preview_enabled;
	// We hide the info about Lychee SE if the user is already a supporter
	// or if they asked to hide it (because we are nice :) ).
	public bool $is_se_info_hidden;

	// Live Metrics settings
	public bool $is_live_metrics_enabled;

	public bool $is_basic_auth_enabled = true;
	public bool $is_webauthn_enabled = true;
	// User registration enabled
	public bool $is_registration_enabled;

	// Gesture settings
	public bool $is_scroll_to_navigate_photos_enabled;
	public bool $is_swipe_vertically_to_go_back_enabled;

	// Homepage
	public string $default_homepage;

	public function __construct()
	{
		// Debug mode
		$this->is_debug_enabled = config('app.debug');

		// NSFW settings
		$this->are_nsfw_visible = Configs::getValueAsBool('nsfw_visible');
		$this->is_nsfw_background_blurred = Configs::getValueAsBool('nsfw_blur'); // blur the thumbnails
		$this->nsfw_banner_override = Configs::getValueAsString('nsfw_banner_override'); // override the banner text.
		$this->is_nsfw_banner_backdrop_blurred = Configs::getValueAsBool('nsfw_banner_blur_backdrop'); // blur the backdrop of the warning banner.

		// keybinding help popup
		$this->show_keybinding_help_popup = Configs::getValueAsBool('show_keybinding_help_popup');

		// Image overlay settings
		$this->image_overlay_type = Configs::getValueAsEnum('image_overlay_type', ImageOverlayType::class);
		$this->can_rotate = Configs::getValueAsBool('editor_enabled');
		$this->can_autoplay = Configs::getValueAsBool('autoplay_enabled');
		$this->is_exif_disabled = Configs::getValueAsBool('exif_disabled_for_all');
		$this->is_favourite_enabled = Configs::getValueAsBool('client_side_favourite_enabled');
		$this->photo_previous_next_size = Configs::getValueAsEnum('photo_previous_next_size', SmallLargeType::class);
		$this->is_details_links_enabled = false;
		if (Configs::getValueAsBool('details_links_enabled')) {
			$this->is_details_links_enabled = !Auth::guest() || Configs::getValueAsBool('details_links_public');
		}

		// Thumbs configuration
		$this->display_thumb_album_overlay = Configs::getValueAsEnum('display_thumb_album_overlay', ThumbOverlayVisibilityType::class);
		$this->display_thumb_photo_overlay = Configs::getValueAsEnum('display_thumb_photo_overlay', ThumbOverlayVisibilityType::class);
		$this->album_subtitle_type = Configs::getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class);
		$this->album_decoration = Configs::getValueAsEnum('album_decoration', AlbumDecorationType::class);
		$this->album_decoration_orientation = Configs::getValueAsEnum('album_decoration_orientation', AlbumDecorationOrientation::class);
		$this->number_albums_per_row_mobile = Configs::getValueAsInt('number_albums_per_row_mobile');
		$this->photo_thumb_info = Configs::getValueAsEnum('photo_thumb_info', PhotoThumbInfoType::class);

		// Download configuration
		$this->is_thumb_download_enabled = Configs::getValueAsBool('disable_thumb_download') === false;
		$this->is_thum2x_download_enabled = Configs::getValueAsBool('disable_thumb2x_download') === false;
		$this->is_small_download_enabled = Configs::getValueAsBool('disable_small_download') === false;
		$this->is_small2x_download_enabled = Configs::getValueAsBool('disable_small2x_download') === false;
		$this->is_medium_download_enabled = Configs::getValueAsBool('disable_medium_download') === false;
		$this->is_medium2x_download_enabled = Configs::getValueAsBool('disable_medium2x_download') === false;

		// Clockwork
		$this->has_clockwork_in_menu();

		// Slideshow settings
		$this->slideshow_timeout = Configs::getValueAsInt('slideshow_timeout');
		$this->is_slideshow_enabled = Configs::getValueAsBool('slideshow_enabled');

		// Timeline settings
		$this->is_timeline_left_border_visible = Configs::getValueAsBool('timeline_left_border_enabled');

		// Site title & dropbox key if logged in as admin.
		$this->title = Configs::getValueAsString('site_title');
		$this->dropbox_api_key = Auth::user()?->may_administrate === true ? Configs::getValueAsString('dropbox_key') : 'disabled';

		$this->is_basic_auth_enabled = AuthServiceProvider::isBasicAuthEnabled();
		$this->is_webauthn_enabled = AuthServiceProvider::isWebAuthnEnabled();
		// User registration enabled
		$this->is_registration_enabled = Configs::getValueAsBool('user_registration_enabled');

		// Gesture settings
		$this->is_scroll_to_navigate_photos_enabled = Configs::getValueAsBool('is_scroll_to_navigate_photos_enabled');
		$this->is_swipe_vertically_to_go_back_enabled = Configs::getValueAsBool('is_swipe_vertically_to_go_back_enabled');

		// Homepage
		$this->default_homepage = Configs::getValueAsString('home_page_default');

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
		$verify = resolve(Verify::class);
		$is_supporter = $verify->is_supporter();

		// We enable Lychee SE if the user is a supporter.
		$this->is_se_enabled = $verify->validate() && $is_supporter;

		// We disable preview if we are already a supporter.
		$this->is_se_preview_enabled = !$is_supporter && Configs::getValueAsBool('enable_se_preview');

		// We hide the info if we are already a supporter (or the user requests it).
		$this->is_se_info_hidden = $is_supporter || Configs::getValueAsBool('disable_se_call_for_actions');

		$this->is_live_metrics_enabled = $this->is_se_enabled && Configs::getValueAsBool('live_metrics_enabled');
	}
}