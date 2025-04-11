<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\TimelineAlbumGranularity;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RootConfig extends Data
{
	public bool $is_album_timeline_enabled = false;
	public bool $is_search_accessible = false;
	public bool $show_keybinding_help_button = false;
	#[LiteralTypeScriptType('App.Enum.AspectRatioType')]
	public AspectRatioCSSType $album_thumb_css_aspect_ratio;

	// for now we keep it here. Maybe we should move it to a separate class
	public string $login_button_position; // left/right
	public bool $back_button_enabled;
	public string $back_button_text;
	public string $back_button_url;
	public TimelineAlbumGranularity $timeline_album_granularity;

	public function __construct()
	{
		$is_logged_in = Auth::check();

		$timeline_albums_enabled = Configs::getValueAsBool('timeline_albums_enabled');
		$timeline_albums_public = Configs::getValueAsBool('timeline_albums_public');
		$this->is_album_timeline_enabled = $timeline_albums_enabled && ($is_logged_in || $timeline_albums_public);
		$this->timeline_album_granularity = Configs::getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class);

		$this->is_search_accessible = $is_logged_in || Configs::getValueAsBool('search_public');

		$this->album_thumb_css_aspect_ratio = Configs::getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
		$this->show_keybinding_help_button = Configs::getValueAsBool('show_keybinding_help_button');
		$this->login_button_position = Configs::getValueAsString('login_button_position');
		$this->back_button_enabled = Configs::getValueAsBool('back_button_enabled');
		$this->back_button_text = Configs::getValueAsString('back_button_text');
		$this->back_button_url = Configs::getValueAsString('back_button_url');
	}
}

