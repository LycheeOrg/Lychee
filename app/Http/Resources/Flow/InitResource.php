<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Enum\CoverFitType;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use LycheeVerify\Verify;
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
		$is_supporter = resolve(Verify::class)->check();
		$this->is_mod_flow_enabled = Configs::getValueAsBool('flow_enabled') && (Auth::check() || Configs::getValueAsBool('flow_public'));
		$this->is_display_open_album_button = Configs::getValueAsBool('flow_display_open_album_button');
		$this->is_open_album_on_click = $is_supporter && Configs::getValueAsBool('flow_open_album_on_click');
		$this->is_highlight_first_picture = Configs::getValueAsBool('flow_highlight_first_picture');
		$this->is_image_header_enabled = Configs::getValueAsBool('flow_image_header_enabled');
		$this->image_header_cover = Configs::getValueAsEnum('flow_image_header_cover', CoverFitType::class);
		$this->image_header_height = Configs::getValueAsInt('flow_image_header_height');
		$this->is_carousel_enabled = Configs::getValueAsBool('flow_carousel_enabled');
		$this->carousel_height = Configs::getValueAsInt('flow_carousel_height');
		$this->is_blur_nsfw_enabled = Configs::getValueAsBool('flow_blur_nsfw_enabled');
		$this->is_compact_mode_enabled = $is_supporter && Configs::getValueAsBool('flow_compact_mode_enabled');
	}
}