<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Enum\CoverFitType;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class InitResource extends Data
{
	public bool $is_mod_flow_enabled;
	public bool $is_open_album_on_click;
	public bool $is_display_open_album_button;
	public bool $is_highlight_first_picture;
	public bool $is_image_header_enabled;

	public CoverFitType $image_header_cover;
	public int $image_header_height;
	public bool $is_carousel_enabled;
	public int $carousel_height;
	public bool $is_blur_nsfw_enabled;
	public bool $is_compact_mode_enabled;

	/**
	 * @return void
	 */
	public function __construct()
	{
		$is_supporter = request()->verify()->check();
		$this->is_mod_flow_enabled = request()->configs()->getValueAsBool('flow_enabled') && (Auth::check() || request()->configs()->getValueAsBool('flow_public'));
		$this->is_display_open_album_button = request()->configs()->getValueAsBool('flow_display_open_album_button');
		$this->is_open_album_on_click = $is_supporter && request()->configs()->getValueAsBool('flow_open_album_on_click');
		$this->is_highlight_first_picture = request()->configs()->getValueAsBool('flow_highlight_first_picture');
		$this->is_image_header_enabled = request()->configs()->getValueAsBool('flow_image_header_enabled');
		$this->image_header_cover = request()->configs()->getValueAsEnum('flow_image_header_cover', CoverFitType::class);
		$this->image_header_height = request()->configs()->getValueAsInt('flow_image_header_height');
		$this->is_carousel_enabled = request()->configs()->getValueAsBool('flow_carousel_enabled');
		$this->carousel_height = request()->configs()->getValueAsInt('flow_carousel_height');
		$this->is_blur_nsfw_enabled = request()->configs()->getValueAsBool('flow_blur_nsfw_enabled');
		$this->is_compact_mode_enabled = $is_supporter && request()->configs()->getValueAsBool('flow_compact_mode_enabled');
	}
}