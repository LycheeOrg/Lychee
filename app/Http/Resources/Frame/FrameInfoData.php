<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Frame;

use App\Enum\FrameBoxPosition;
use App\Repositories\ConfigManager;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Admin values of the Frame info box settings (Feature 088, DO-088-03).
 * The v8 Frame applies the URL overrides on top of them (ADR-088-01).
 */
#[TypeScript()]
class FrameInfoData extends Data
{
	public function __construct(
		public bool $is_title_enabled,
		public bool $is_albums_enabled,
		public bool $is_exif_enabled,
		public bool $is_exif_exposure_enabled,
		public bool $is_exif_camera_enabled,
		public bool $is_exif_taken_at_enabled,
		public FrameBoxPosition $position,
		public string $background_color,
		public int $background_opacity,
		public int $opacity,
	) {
	}

	public static function fromConfigs(ConfigManager $configs): self
	{
		return new self(
			is_title_enabled: $configs->getValueAsBool('mod_frame_info_title_enabled'),
			is_albums_enabled: $configs->getValueAsBool('mod_frame_info_albums_enabled'),
			is_exif_enabled: $configs->getValueAsBool('mod_frame_info_exif_enabled'),
			is_exif_exposure_enabled: $configs->getValueAsBool('mod_frame_info_exif_exposure_enabled'),
			is_exif_camera_enabled: $configs->getValueAsBool('mod_frame_info_exif_camera_enabled'),
			is_exif_taken_at_enabled: $configs->getValueAsBool('mod_frame_info_exif_taken_at_enabled'),
			position: FrameBoxPosition::from($configs->getValueAsString('mod_frame_info_position')),
			background_color: $configs->getValueAsString('mod_frame_info_background_color'),
			background_opacity: $configs->getValueAsInt('mod_frame_info_background_opacity'),
			opacity: $configs->getValueAsInt('mod_frame_info_opacity'),
		);
	}
}
