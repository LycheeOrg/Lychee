<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Insights;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Devices section of Insights (Feature 085, FR-085-12, FR-085-18), sorted by
 * count; `focal_lengths` lists the devices with a focal length.
 */
#[TypeScript()]
class DevicesData extends Data
{
	/**
	 * @param DeviceEntryData[] $devices
	 * @param DeviceEntryData[] $manufacturers
	 * @param DeviceEntryData[] $lenses
	 * @param DeviceFocalData[] $focal_lengths
	 */
	public function __construct(
		public array $devices,
		public array $manufacturers,
		public array $lenses,
		public array $focal_lengths,
	) {
	}
}
