<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use App\Enum\DeviceCategory;

/**
 * A capture device after normalisation. `null` names mean "unknown".
 */
final readonly class Device
{
	public function __construct(
		public ?string $manufacturer,
		public ?string $name,
		public DeviceCategory $category,
	) {
	}
}
