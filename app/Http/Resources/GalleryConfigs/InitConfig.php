<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\ImageOverlayType;
use App\Enum\ThumbOverlayVisibilityType;
use App\Models\Configs;
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
	public ImageOverlayType $image_overlay_type;
	public ThumbOverlayVisibilityType $display_thumb_album_overlay;
	public ThumbOverlayVisibilityType $display_thumb_photo_overlay;

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
	}
}