<?php

namespace App\Actions\InstallUpdate;

use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Contracts\Versions\GitHubVersionControl;
use App\Metadata\Versions\LycheeVersion;

class CheckUpdate
{
	private GitHubVersionControl $gitHubFunctions;
	private LycheeVersion $lycheeVersion;

	/**
	 * @param GitHubVersionControl $gitHubFunctions
	 * @param LycheeVersion        $lycheeVersion
	 */
	public function __construct(
		GitHubVersionControl $gitHubFunctions,
		LycheeVersion $lycheeVersion
	) {
		$this->gitHubFunctions = $gitHubFunctions;
		$this->lycheeVersion = $lycheeVersion;
		$gitHubFunctions->hydrate();
	}

	/**
	 * CheckUpdate for updates, return text or an exception if not possible.
	 */
	public function getText(): string
	{
		$this->gitHubFunctions->hydrate(true, false);

		return $this->gitHubFunctions->getBehindTest();
	}

	/**
	 * CheckUpdate for updates and returns the update state.
	 *
	 * The return codes have the following semantics:
	 *  - `0` - Not on master branch
	 *  - `1` - Up-to-date
	 *  - `2` - Not up-to-date.
	 *  - `3` - Require migration.
	 *
	 * The following line of codes are duplicated in
	 *  - {@link \App\Actions\Diagnostics\Checks\LycheeDBVersionCheck::check()}
	 *  - {@link \App\Http\Middleware\Checks\IsMigrated::assert()}.
	 *
	 * TODO: Probably, the whole logic around installation and updating should be re-factored. The whole code is wicked.
	 *
	 * @return int the update state between 0..3
	 */
	public function getCode(): int
	{
		if ($this->lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			$db_ver = $this->lycheeVersion->getDBVersion();
			$file_ver = $this->lycheeVersion->getFileVersion();

			return 3 * intval($db_ver->toInteger() < $file_ver->toInteger());
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
