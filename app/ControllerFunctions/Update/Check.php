<?php

namespace App\ControllerFunctions\Update;

use App\Configs;
use App\Exceptions\ExecNotAvailable;
use App\Exceptions\GitNotAvailable;
use App\Exceptions\NoOnlineUpdate;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
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
	 * @param GitHubFunctions $gitHubFunctions
	 */
	public function __construct(
		GitHubFunctions $gitHubFunctions,
		GitRequest $gitRequest
	) {
		$this->gitHubFunctions = $gitHubFunctions;
		$this->gitRequest = $gitRequest;
	}

	public function canUpdate()
	{
		if (Configs::get_value('allow_online_git_pull', '0') == '0') {
			throw new NoOnlineUpdate();
		}

		// When going with the CI, .git is always executable and exec is also available
		// @codeCoverageIgnoreStart
		if (!function_exists('exec')) {
			throw new ExecNotAvailable();
		}
		if (!$this->gitHubFunctions->is_usable()) {
			throw new GitNotAvailable();
		}
		// @codeCoverageIgnoreFalse

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
		} catch (Exception $e) {
			return false;
		}
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
			return $this->gitHubFunctions->get_behind_text();
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
	 */
	public function getCode()
	{
		$update = $this->canUpdateBool();

		if ($update) {
			try {
				if (!$this->gitHubFunctions->is_up_to_date()) {
					return 2;
				} else {
					return 1;
				}
			} catch (NotInCacheException $e) {
				return 1;
			} catch (NotMasterException $e) {
				return 0;
			}
		}
	}
}