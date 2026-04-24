<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\UpdateStatus;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AdminUpdateStatusResource extends Data
{
	public function __construct(
		public bool $enabled,
		public ?int $update_status,
		public bool $has_update,
		public ?string $current_version,
		public ?string $latest_version,
	) {
	}

	public static function disabled(): self
	{
		return new self(
			enabled: false,
			update_status: null,
			has_update: false,
			current_version: null,
			latest_version: null,
		);
	}

	public static function fromUpdateStatus(UpdateStatus $update_status, string $current_version, string $latest_version): self
	{
		return new self(
			enabled: true,
			update_status: $update_status->value,
			has_update: $update_status === UpdateStatus::NOT_UP_TO_DATE,
			current_version: $current_version,
			latest_version: $latest_version,
		);
	}
}