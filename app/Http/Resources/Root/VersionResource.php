<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Root;

use App\Actions\InstallUpdate\CheckUpdateAvailability;
use App\Metadata\Versions\InstalledVersion;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class VersionResource extends Data
{
	public ?string $version = null;
	public bool $is_new_release_available;
	public bool $is_git_update_available;

	public function __construct()
	{
		if (!request()->configs()->getValueAsBool('hide_version_number')) {
			$this->version = resolve(InstalledVersion::class)->getVersion()->toString();
		}

		$availability = resolve(CheckUpdateAvailability::class)->get();
		$this->is_new_release_available = $availability->is_new_release_available;
		$this->is_git_update_available = $availability->is_git_update_available;
	}
}
