<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\ImageOverlayType;
use App\Enum\ThumbAlbumSubtitleType;
use App\Enum\ThumbOverlayVisibilityType;
use App\Models\Configs;
use Illuminate\Support\Facades\URL;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class InitConfig extends Data
{
	public bool $are_nsfw_visible;
	public bool $are_nsfw_blurred;
	public bool $is_nsfw_warning_visible;
	public bool $is_nsfw_warning_visible_for_admin;
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

	public function __construct()
	{
		$this->are_nsfw_visible = Configs::getValueAsBool('nsfw_visible');
		$this->are_nsfw_blurred = Configs::getValueAsBool('nsfw_blur');
		$this->is_nsfw_warning_visible = Configs::getValueAsBool('nsfw_warning');
		$this->is_nsfw_warning_visible_for_admin = Configs::getValueAsBool('nsfw_warning_admin');
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

		$this->album_decoration = Configs::getValueAsEnum('album_decoration', AlbumDecorationType::class);
		$this->album_decoration_orientation = Configs::getValueAsEnum('album_decoration_orientation', AlbumDecorationOrientation::class);

		$this->title = Configs::getValueAsString('site_title');
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