<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Frame;

use App\Enum\FrameBoxPosition;
use App\Enum\FrameTimeFormat;
use App\Repositories\ConfigManager;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Admin values of the Frame clock box settings (Feature 088, DO-088-02).
 * The v8 Frame applies the URL overrides on top of them (ADR-088-01).
 */
#[TypeScript()]
class FrameClockData extends Data
{
	public function __construct(
		public bool $is_time_enabled,
		public FrameTimeFormat $time_format,
		public bool $is_date_enabled,
		public FrameBoxPosition $position,
		public string $background_color,
		public int $background_opacity,
		public int $opacity,
	) {
	}

	public static function fromConfigs(ConfigManager $configs): self
	{
		return new self(
			is_time_enabled: $configs->getValueAsBool('mod_frame_clock_time_enabled'),
			time_format: $configs->getValueAsEnum('mod_frame_clock_time_format', FrameTimeFormat::class) ?? FrameTimeFormat::H24,
			is_date_enabled: $configs->getValueAsBool('mod_frame_clock_date_enabled'),
			position: $configs->getValueAsEnum('mod_frame_clock_position', FrameBoxPosition::class) ?? FrameBoxPosition::BOTTOM_RIGHT,
			background_color: $configs->getValueAsString('mod_frame_clock_background_color'),
			background_opacity: $configs->getValueAsInt('mod_frame_clock_background_opacity'),
			opacity: $configs->getValueAsInt('mod_frame_clock_opacity'),
		);
	}
}
