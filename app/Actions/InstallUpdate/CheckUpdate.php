<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Enum\UpdateStatus;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;

class CheckUpdate
{
	public function __construct(
		private GitHubVersion $git_hub_functions,
		private InstalledVersion $installed_version,
		private FileVersion $file_version,
	) {
		$this->git_hub_functions->hydrate();
		$this->file_version->hydrate();
		$this->installed_version = $installed_version;
	}

	/**
	 * Check for updates and returns the update state.
	 *
	 * The return codes have the following semantics:
	 *  - `0` - Not on master branch
	 *  - `1` - Up-to-date
	 *  - `2` - Not up-to-date.
	 *  - `3` - Require migration.
	 *
	 * @return UpdateStatus the update state between 0..3
	 */
	public function getCode(): UpdateStatus
	{
		if ($this->installed_version->isRelease()) {
			// @codeCoverageIgnoreStart
			return match (false) {
				MigrationCheck::isUpToDate() => UpdateStatus::REQUIRE_MIGRATION,
				$this->file_version->isUpToDate() => UpdateStatus::NOT_UP_TO_DATE,
				default => UpdateStatus::UP_TO_DATE,
			};
			// @codeCoverageIgnoreEnd
		}

		try {
			UpdatableCheck::assertUpdatability();
			// @codeCoverageIgnoreStart
			if (!$this->git_hub_functions->isUpToDate()) {
				return UpdateStatus::NOT_UP_TO_DATE;
			} else {
				return UpdateStatus::UP_TO_DATE;
			}
			// @codeCoverageIgnoreEnd
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			return UpdateStatus::NOT_MASTER;
		}
		// @codeCoverageIgnoreEnd
	}
}