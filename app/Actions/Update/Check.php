<?php

namespace App\Actions\Update;

use App\Contracts\GitHubVersionControl;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Metadata\LycheeVersion;
use App\Models\Configs;
use function Safe\exec;

class Check
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
	 * Ensures that Lychee can be updated or throws an exception otherwise.
	 *
	 * @return void
	 *
	 * @throws ConfigurationException
	 * @throws ExternalComponentMissingException
	 * @throws InsufficientFilesystemPermissions
	 */
	public function assertUpdatability(): void
	{
		// we bypass this because we don't care about the other conditions as they don't apply to the release
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		if (!Configs::getValueAsBool('allow_online_git_pull')) {
			throw new ConfigurationException('Online updates are disabled by configuration');
		}

		// When going with the CI, .git is always executable
		// @codeCoverageIgnoreStart
		if (exec('command -v git') === '') {
			throw new ExternalComponentMissingException('git (software) is not available.');
		}

		if (!$this->gitHubFunctions->hasPermissions()) {
			throw new InsufficientFilesystemPermissions(base_path('.git') . ' (and subdirectories) are not executable, check the permissions');
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Check for updates, return text or an exception if not possible.
	 */
	public function getText(): string
	{
		$this->gitHubFunctions->hydrate(false);

		return $this->gitHubFunctions->getBehindTest();
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
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			$db_ver = $this->lycheeVersion->getDBVersion();
			$file_ver = $this->lycheeVersion->getFileVersion();

			return 3 * intval($db_ver->toInteger() < $file_ver->toInteger());
			// @codeCoverageIgnoreEnd
		}

		try {
			$this->assertUpdatability();
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
