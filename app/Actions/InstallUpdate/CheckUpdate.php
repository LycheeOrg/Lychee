<?php

namespace App\Actions\InstallUpdate;

use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Contracts\Versions\GitHubVersionControl;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\LycheeVersion;

class CheckUpdate
{
	/**
	 * @param GitHubVersionControl $gitHubFunctions
	 * @param LycheeVersion        $lycheeVersion
	 * @param FileVersion          $fileVersion
	 */
	public function __construct(
		private GitHubVersionControl $gitHubFunctions,
		private LycheeVersion $lycheeVersion,
		private FileVersion $fileVersion,
	) {
		$this->gitHubFunctions->hydrate();
		$this->fileVersion->hydrate();
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
	 * @return int the update state between 0..3
	 */
	public function getCode(): int
	{
		if ($this->lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			return match (false) {
				MigrationCheck::isUpToDate() => 3,
				$this->fileVersion->isUpToDate() => 2,
				default => 1
			};
			// @codeCoverageIgnoreEnd
		}

		try {
			UpdatableCheck::assertUpdatability();
			// @codeCoverageIgnoreStart
			if (!$this->gitHubFunctions->isUpToDate()) {
				return 2;
			} else {
				return 1;
			}
			// @codeCoverageIgnoreEnd
		} catch (\Exception $e) {
			return 0;
		}
	}
}
