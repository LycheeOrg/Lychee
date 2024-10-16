<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\ThumbAlbumSubtitleType;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RootConfig extends Data
{
	public bool $is_map_accessible = false;
	public bool $is_mod_frame_enabled = false;
	public bool $is_search_accessible = false;
	#[LiteralTypeScriptType('App.Enum.AspectRatioType')]
	public AspectRatioCSSType $album_thumb_css_aspect_ratio;
	public ThumbAlbumSubtitleType $album_subtitle_type;

	public function __construct()
	{
		$count_locations = Photo::whereNotNull('latitude')->whereNotNull('longitude')->count() > 0;
		$map_display = Configs::getValueAsBool('map_display');
		$public_display = Auth::check() || Configs::getValueAsBool('map_display_public');
		$this->is_map_accessible = $count_locations && $map_display && $public_display;
		$this->is_mod_frame_enabled = Configs::getValueAsBool('mod_frame_enabled');
		$this->is_search_accessible = Auth::check() || Configs::getValueAsBool('search_public');
		$this->album_thumb_css_aspect_ratio = Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
		$this->album_subtitle_type = Configs::getValueAsEnum('album_subtitle_type', ThumbAlbumSubtitleType::class);
	}
}
