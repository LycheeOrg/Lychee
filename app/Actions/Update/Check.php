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
use Exception;

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
	 * Returns true or throws an exception.
	 *
	 * @throws ConfigurationException
	 * @throws ExternalComponentMissingException
	 * @throws InsufficientFilesystemPermissions
	 */
	public function canUpdate(): bool
	{
		// we bypass this because we don't care about the other conditions as they don't apply to the release
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			return true;
			// @codeCoverageIgnoreEnd
		}

		if (Configs::get_value('allow_online_git_pull', '0') == '0') {
			throw new ConfigurationException('Online updates are disabled by configuration');
		}

		// When going with the CI, .git is always executable
		// @codeCoverageIgnoreStart
		if (exec('command -v git') == '') {
			throw new ExternalComponentMissingException('git (software) is not available.');
		}

		if (!$this->gitHubFunctions->has_permissions()) {
			throw new InsufficientFilesystemPermissions(base_path('.git') . ' (and subdirectories) are not executable, check the permissions');
		}
		// @codeCoverageIgnoreEnd

		return true;
	}

	/**
	 * Catch the Exception and return the boolean equivalent.
	 *
	 * @return bool
	 */
	private function canUpdateBool(): bool
	{
		try {
			return $this->canUpdate();
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return false;
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
	 * Check for updates, returns the code
	 * 0 - Not Master
	 * 1 - Up-to-date
	 * 2 - Not up to date.
	 * 3 - Require migration.
	 */
	public function getCode(): int
	{
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			$versions = $this->lycheeVersion->get();

			return 3 * intval($versions['DB']['version'] < $versions['Lychee']['version']);
			// @codeCoverageIgnoreEnd
		}

		$update = $this->canUpdateBool();

		if ($update) {
			try {
				// @codeCoverageIgnoreStart
				if (!$this->gitHubFunctions->is_up_to_date()) {
					return 2;
				} else {
					return 1;
				}
				// @codeCoverageIgnoreEnd
			} catch (VersionControlException $e) {
				return 0;
			}
		}

		return 0;
	}
}
