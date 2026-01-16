<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\AspectRatioCSSType;
use App\Enum\AspectRatioType;
use App\Enum\SharedAlbumsVisibility;
use App\Enum\TimelineAlbumGranularity;
use App\Models\User;
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
	public bool $back_button_enabled;
	public string $back_button_text;
	public string $back_button_url;
	public TimelineAlbumGranularity $timeline_album_granularity;

	public string $header_image_url = '';
	public bool $is_header_bar_transparent = false;
	public bool $is_header_bar_gradient = false;

	public SharedAlbumsVisibility $shared_albums_visibility_mode = SharedAlbumsVisibility::SHOW;

	public function __construct()
	{
		$is_logged_in = Auth::check();

		$timeline_albums_enabled = request()->configs()->getValueAsBool('timeline_albums_enabled');
		$timeline_albums_public = request()->configs()->getValueAsBool('timeline_albums_public');
		$this->is_album_timeline_enabled = $timeline_albums_enabled && ($is_logged_in || $timeline_albums_public);
		$this->timeline_album_granularity = request()->configs()->getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class);

		$this->is_search_accessible = $is_logged_in || request()->configs()->getValueAsBool('search_public');

		$this->album_thumb_css_aspect_ratio = request()->configs()->getValueAsEnum('default_album_thumb_aspect_ratio', AspectRatioType::class)->css();
		$this->show_keybinding_help_button = request()->configs()->getValueAsBool('show_keybinding_help_button');
		$this->back_button_enabled = request()->configs()->getValueAsBool('back_button_enabled');
		$this->back_button_text = request()->configs()->getValueAsString('back_button_text');
		$this->back_button_url = request()->configs()->getValueAsString('back_button_url');

		$this->setHeaderImageUrl();
		$this->setSharedAlbumsVisibilityMode();
	}

	private function setHeaderImageUrl(): void
	{
		if (!request()->configs()->getValueAsBool('gallery_header_enabled')) {
			return;
		}
		if (Auth::check() && !request()->configs()->getValueAsBool('gallery_header_logged_in_enabled')) {
			return;
		}
		$this->header_image_url = request()->configs()->getValueAsString('gallery_header');
		$this->is_header_bar_transparent = request()->configs()->getValueAsBool('gallery_header_bar_transparent');
		$this->is_header_bar_gradient = request()->configs()->getValueAsBool('gallery_header_bar_gradient');
	}

	private function setSharedAlbumsVisibilityMode(): void
	{
		/** @var User|null $user */
		$user = Auth::user();
		if ($user === null) {
			// For guests, shared albums visibility is not applicable
			return;
		}

		$this->shared_albums_visibility_mode = $user->shared_albums_visibility->tooSharedAlbumsVisibility();
	}
}

