<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Root;

use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
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
		if (!Configs::getValueAsBool('hide_version_number')) {
			$this->version = resolve(InstalledVersion::class)->getVersion()->toString();
		}

		$file_version = resolve(FileVersion::class);
		$git_hub_version = resolve(GitHubVersion::class);

		if (Configs::getValueAsBool('check_for_updates')) {
			// @codeCoverageIgnoreStart
			$file_version->hydrate();
			$git_hub_version->hydrate();
			// @codeCoverageIgnoreEnd
		}

		$this->is_new_release_available = !$file_version->isUpToDate();
		$this->is_git_update_available = !$git_hub_version->isUpToDate();
	}
}
