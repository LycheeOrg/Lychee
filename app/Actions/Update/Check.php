<?php

namespace App\Actions\Update;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\VersionControlException;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\Models\Configs;
use function Safe\exec;

class Check
{
	private GitHubFunctions $gitHubFunctions;
	private GitRequest $gitRequest;
	private LycheeVersion $lycheeVersion;

	/**
	 * @param GitHubFunctions $gitHubFunctions
	 * @param GitRequest      $gitRequest
	 * @param LycheeVersion   $lycheeVersion
	 */
	public function __construct(
		GitHubFunctions $gitHubFunctions,
		GitRequest $gitRequest,
		LycheeVersion $lycheeVersion
	) {
		$this->gitHubFunctions = $gitHubFunctions;
		$this->gitRequest = $gitRequest;
		$this->lycheeVersion = $lycheeVersion;
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

		if (!$this->gitHubFunctions->has_permissions()) {
			throw new InsufficientFilesystemPermissions(base_path('.git') . ' (and subdirectories) are not executable, check the permissions');
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Clear cache and check if up to date.
	 *
	 * @return bool
	 *
	 * @throws VersionControlException
	 */
	private function forget_and_check(): bool
	{
		$this->gitRequest->clear_cache();

		return $this->gitHubFunctions->is_up_to_date(false);
	}

	/**
	 * Check for updates, return text or an exception if not possible.
	 *
	 * @throws VersionControlException
	 */
	public function getText(): string
	{
		$up_to_date = $this->forget_and_check();

		if (!$up_to_date) {
			// @codeCoverageIgnoreStart
			return $this->gitHubFunctions->get_behind_text();
		// @codeCoverageIgnoreEnd
		} else {
			return 'Already up to date';
		}
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
	 * @return UpdateStatus the update state between 0..3
	 */
	public function getCode(): UpdateStatus
	{
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			$db_ver = $this->lycheeVersion->getDBVersion();
			$file_ver = $this->lycheeVersion->getFileVersion();

			return $db_ver->toInteger() < $file_ver->toInteger() ? UpdateStatus::REQUIRE_MIGRATION : UpdateStatus::NOT_MASTER;
			// @codeCoverageIgnoreEnd
		}

		try {
			$this->assertUpdatability();
			// @codeCoverageIgnoreStart
			if (!$this->gitHubFunctions->is_up_to_date()) {
				return UpdateStatus::NOT_UP_TO_DATE;
			} else {
				return UpdateStatus::UP_TO_DATE;
			}
			// @codeCoverageIgnoreEnd
		} catch (\Exception $e) {
			return UpdateStatus::NOT_MASTER;
		}
	}
}
