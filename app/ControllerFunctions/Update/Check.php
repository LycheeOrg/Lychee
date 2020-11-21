<?php

namespace App\ControllerFunctions\Update;

use App\Exceptions\ExecNotAvailableException;
use App\Exceptions\GitNotAvailableException;
use App\Exceptions\GitNotExecutableException;
use App\Exceptions\NoOnlineUpdateException;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\Models\Configs;
use Exception;

class Check
{
	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var GitRequest
	 */
	private $gitRequest;

	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @param GitHubFunctions $gitHubFunctions
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
	 * @throws NoOnlineUpdateException
	 * @throws GitNotAvailableException
	 * @throws ExecNotAvailableException
	 * @throws GitNotExecutableException
	 */
	public function canUpdate()
	{
		// we bypass this because we don't care about the other conditions as they don't apply to the release
		if ($this->lycheeVersion->isRelease) {
			// @codeCoverageIgnoreStart
			return true;
			// @codeCoverageIgnoreEnd
		}

		if (Configs::get_value('allow_online_git_pull', '0') == '0') {
			throw new NoOnlineUpdateException();
		}

		// When going with the CI, .git is always executable and exec is also available
		// @codeCoverageIgnoreStart
		if (!function_exists('exec')) {
			throw new ExecNotAvailableException();
		}
		if (exec('command -v git') == '') {
			throw new GitNotAvailableException();
		}

		if (!$this->gitHubFunctions->has_permissions()) {
			throw new GitNotExecutableException();
		}
		// @codeCoverageIgnoreEnd

		return true;
	}

	/**
	 * Cath the Exception and return the boolean equivalent.
	 *
	 * @return bool
	 */
	private function canUpdateBool()
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
	 * @throws NotMasterException
	 * @throws NotInCacheException
	 */
	private function forget_and_check()
	{
		$this->gitRequest->clear_cache();

		return $this->gitHubFunctions->is_up_to_date(false);
	}

	/**
	 * Check for updates, return text or an exception if not possible.
	 *
	 * @throws NotMasterException
	 * @throws NotInCacheException
	 */
	public function getText()
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
	 * 1 - Not in cache
	 * 1 - Up to date
	 * 2 - Not up to date.
	 * 3 - Require migration.
	 */
	public function getCode()
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
			} catch (NotInCacheException $e) {
				return 1;
				// @codeCoverageIgnoreStart
			} catch (NotMasterException $e) {
				return 0;
			}
		}

		return 0;
		// @codeCoverageIgnoreEnd
	}
}
