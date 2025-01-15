<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use App\Enum\VersionChannelType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UpdateInfo extends Data
{
	public function __construct(
		public string $info,
		public string $extra,
		public VersionChannelType $channelName,
		public bool $isDocker,
	) {
	}
}
