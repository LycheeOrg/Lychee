<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\ImageOverlayType;
use App\Enum\ThumbAlbumSubtitleType;
use App\Enum\ThumbOverlayVisibilityType;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use LycheeVerify\Verify;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class InitConfig extends Data
{
	public bool $is_debug_enabled;
	public bool $are_nsfw_visible;
	public bool $is_nsfw_warning_visible;
	public bool $is_nsfw_background_blurred;
	public string $nsfw_banner_override;
	public bool $is_nsfw_banner_backdrop_blurred;
	public bool $show_keybinding_help_popup;
	public ImageOverlayType $image_overlay_type;
	public ThumbOverlayVisibilityType $display_thumb_album_overlay;
	public ThumbOverlayVisibilityType $display_thumb_photo_overlay;
	public ?string $clockwork_url;
	public ThumbAlbumSubtitleType $album_subtitle_type;
	public bool $can_rotate;
	public bool $can_autoplay;
	public AlbumDecorationType $album_decoration;
	public AlbumDecorationOrientation $album_decoration_orientation;
	public string $title;
	public string $dropbox_api_key;
	public int $slideshow_timeout;

	// Lychee SE is available.
	public bool $is_se_enabled;
	// Lychee SE is not available, but preview is enabled.
	public bool $is_se_preview_enabled;
	// We hide the info about Lychee SE if the user is already a supporter
	// or if they asked to hide it (because we are nice :) ).
	public bool $is_se_info_hidden;

	public function __construct()
	{
		$this->is_debug_enabled = config('app.debug');
		$this->are_nsfw_visible = Configs::getValueAsBool('nsfw_visible');
		$this->is_nsfw_background_blurred = Configs::getValueAsBool('nsfw_blur');
		$this->nsfw_banner_override = Configs::getValueAsString('nsfw_banner_override');
		$this->is_nsfw_banner_backdrop_blurred = Configs::getValueAsBool('nsfw_banner_blur_backdrop');
		$this->image_overlay_type = Configs::getValueAsEnum('image_overlay_type', ImageOverlayType::class);
		$this->display_thumb_album_overlay = Configs::getValueAsEnum('display_thumb_album_overlay', ThumbOverlayVisibilityType::class);
		$this->display_thumb_photo_overlay = Configs::getValueAsEnum('display_thumb_photo_overlay', ThumbOverlayVisibilityType::class);
		$this->show_keybinding_help_popup = Configs::getValueAsBool('show_keybinding_help_popup');
		$this->clockwork_url = $this->has_clockwork_in_menu();

		$this->album_subtitle_type = Configs::getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class);
		$this->can_rotate = Configs::getValueAsBool('editor_enabled');
		$this->can_autoplay = Configs::getValueAsBool('autoplay_enabled');
		$this->slideshow_timeout = Configs::getValueAsInt('slideshow_timeout');

		$this->album_decoration = Configs::getValueAsEnum('album_decoration', AlbumDecorationType::class);
		$this->album_decoration_orientation = Configs::getValueAsEnum('album_decoration_orientation', AlbumDecorationOrientation::class);

		$this->title = Configs::getValueAsString('site_title');

		$verify = resolve(Verify::class);
		$is_supporter = $verify->is_supporter();
		$this->is_se_enabled = $verify->validate() && $is_supporter;
		$this->is_se_preview_enabled = !$is_supporter && Configs::getValueAsBool('enable_se_preview');
		$this->is_se_info_hidden = $is_supporter || Configs::getValueAsBool('disable_se_call_for_actions');

		$this->dropbox_api_key = Auth::user()?->may_administrate === true ? Configs::getValueAsString('dropbox_key') : 'disabled';
	}

	private function has_clockwork_in_menu(): string|null
	{
		// Defining clockwork URL
		$clockWorkEnabled = config('clockwork.enable') === true || (config('app.debug') === true && config('clockwork.enable') === null);
		$clockWorkWeb = config('clockwork.web');
		if ($clockWorkEnabled && $clockWorkWeb === true) {
			return URL::asset('clockwork/app');
		}
		if (is_string($clockWorkWeb)) {
			return $clockWorkWeb . '/app';
		}

		return null;
	}
}