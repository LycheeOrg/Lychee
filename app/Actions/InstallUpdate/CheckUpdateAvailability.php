<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Assets\Features;
use App\DTO\UpdateAvailability;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Repositories\ConfigManager;

/**
 * Single source of truth for "is there a newer Lychee?".
 *
 * Remote data is only fetched when both the `update-check` feature
 * and the `check_for_updates` setting are enabled.
 */
class CheckUpdateAvailability
{
	public function __construct(
		private FileVersion $file_version,
		private GitHubVersion $github_version,
		private ConfigManager $config_manager,
	) {
	}

	public function get(): UpdateAvailability
	{
		if (!$this->isEnabled()) {
			return UpdateAvailability::disabled();
		}

		$this->file_version->hydrate();
		$this->github_version->hydrate();
		$count_behind = $this->github_version->getCountBehind();

		return new UpdateAvailability(
			enabled: true,
			is_new_release_available: !$this->file_version->isUpToDate(),
			is_git_update_available: !$this->github_version->isUpToDate(),
			commits_behind: $count_behind === false ? null : $count_behind,
			current_version: $this->file_version->getVersion()->toString(),
			latest_version: $this->file_version->remote_version?->toString(),
		);
	}

	private function isEnabled(): bool
	{
		return Features::active('update-check') && $this->config_manager->getValueAsBool('check_for_updates');
	}
}
