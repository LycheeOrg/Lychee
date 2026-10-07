<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\DTO\UpdateAvailability;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AdminUpdateStatusResource extends Data
{
	public function __construct(
		public bool $enabled,
		public bool $is_new_release_available,
		public bool $is_git_update_available,
		public ?int $commits_behind,
		public ?string $current_version,
		public ?string $latest_version,
	) {
	}

	public static function fromAvailability(UpdateAvailability $availability): self
	{
		return new self(
			enabled: $availability->enabled,
			is_new_release_available: $availability->is_new_release_available,
			is_git_update_available: $availability->is_git_update_available,
			commits_behind: $availability->commits_behind,
			current_version: $availability->current_version,
			latest_version: $availability->latest_version,
		);
	}
}
