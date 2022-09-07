<?php

namespace App\Metadata;

use App\Exceptions\VersionControlException;
use App\Facades\Helpers;
use App\ModelFunctions\JsonRequestFunctions;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use function Safe\file_get_contents;

class GitHubFunctions
{
	private GitRequest $gitRequest;
	protected ?string $localBranch;
	protected ?string $localHead;
	protected ?string $remoteHead;

	/**
	 * Base constructor.
	 *
	 * @param GitRequest $gitRequest
	 */
	public function __construct(GitRequest $gitRequest)
	{
		$this->gitRequest = $gitRequest;
		$this->localBranch = null;
		$this->localHead = null;
		$this->remoteHead = null;
	}

	/**
	 * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
	 *
	 * @param string $commit_id
	 *
	 * @return string
	 */
	private static function trim(string $commit_id): string
	{
		return trim(substr($commit_id, 0, 7));
	}

	/**
	 * return the list of the last 30 commits on the master branch.
	 *
	 * @param bool $cached
	 *
	 * @return array
	 *
	 * @throws VersionControlException
	 */
	private function get_commits(bool $cached = true): array
	{
		try {
			return $this->gitRequest->get_json($cached);
		} catch (\Throwable $e) {
			throw new VersionControlException('Could not get commits', $e);
		}
	}

	/**
	 * Count the number of commits between current version and master/HEAD.
	 * Throws NotMaster if the branch is not ... master
	 * Throws NotInCache if the commits are not cached
	 * Returns between 0 and 30 if we can find the value
	 * Returns false if more than 30 commits behind.
	 *
	 * @param bool $cached
	 *
	 * @return int
	 *
	 * @throws VersionControlException
	 */
	private function count_behind(bool $cached = true): int
	{
		if ($this->getLocalBranch() !== 'master') {
			throw new VersionControlException('Branch is not master, cannot compare.');
		}

		$commits = $this->get_commits($cached);

		$i = 0;
		while ($i < count($commits)) {
			if (self::trim($commits[$i]->sha) === $this->getLocalHead()) {
				break;
			}
			$i++;
		}

		if ($i === count($commits)) {
			throw new VersionControlException('More than 30 commits behind.');
		}

		return $i;
	}

	/**
	 * return the commit id (7 hex digits) of the head if found.
	 *
	 * @return string
	 *
	 * @throws VersionControlException
	 */
	private function getRemoteHead(): string
	{
		if ($this->remoteHead === null) {
			$commits = $this->get_commits();
			$this->remoteHead = self::trim($commits[0]->sha);
		}

		return $this->remoteHead;
	}

	/**
	 * Return a string indicating whether we are up-to-date (used in Diagnostics).
	 *
	 * This function should not throw exceptions !
	 *
	 * @return string
	 */
	public function get_behind_text(): string
	{
		try {
			$count = $this->count_behind();
			$last_update = $this->gitRequest->get_age_text();

			if ($count === 0) {
				return sprintf('Up to date (%s).', $last_update);
			} else {
				return sprintf(
					'%d commits behind master %s (%s)',
					$count,
					$this->getRemoteHead(),
					$last_update
				);
			}
		} catch (VersionControlException $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Check if the repo is up-to-date.
	 *
	 * @param bool $cached
	 *
	 * @return bool
	 *
	 * @throws VersionControlException
	 */
	public function is_up_to_date(bool $cached = true): bool
	{
		return $this->count_behind($cached) === 0;
	}

	/**
	 * Simple check if git is usable or not.
	 *
	 * @return bool
	 */
	public function has_permissions(): bool
	{
		try {
			$localBranch = $this->getLocalBranch();
		} catch (VersionControlException) {
			$localBranch = null;
		}

		try {
			return
				Helpers::hasFullPermissions(base_path('.git')) && (
					$localBranch === null ||
					Helpers::hasPermissions(base_path('.git/refs/heads/' . $localBranch))
				);
		} catch (BindingResolutionException) {
			return false;
		}
	}

	/**
	 * Check for updates (old).
	 *
	 * @return array{update_json: int, update_available: bool}
	 *
	 * @throws VersionControlException
	 */
	public function checkUpdates(): array
	{
		// add a setting to do this check only once per day ?
		if (!Configs::getValueAsBool('check_for_updates')) {
			return [
				'update_json' => 0,
				'update_available' => false,
			];
		}

		try {
			$json = new JsonRequestFunctions(Config::get('urls.update.json'));
			$json = $json->get_json();

			return [
				'update_json' => intval($json->lychee->version),
				'update_available' => (
					Configs::getValueAsInt('version') < $json->lychee->version
				),
			];
		} catch (\Throwable $e) {
			throw new VersionControlException('Could not check for updates.', $e);
		}
	}

	/**
	 * Checks if current branch is the master branch.
	 *
	 * This is used to avoid running git pulls on development branches during tests.
	 *
	 * @return bool
	 */
	public function is_master_branch(): bool
	{
		try {
			return $this->getLocalBranch() === 'master';
		} catch (VersionControlException) {
			return false;
		}
	}

	/**
	 * Returns the name of the locally checked-out branch.
	 *
	 * The method reads `.git/HEAD` and caches the result.
	 * If the branch cannot be determined an exception is thrown.
	 *
	 * @return string
	 *
	 * @throws VersionControlException
	 */
	public function getLocalBranch(): string
	{
		if ($this->localBranch === null) {
			try {
				$head_file = base_path('.git/HEAD');
				$branch = file_get_contents($head_file);
				$branch = explode('/', $branch, 3);

				$this->localBranch = trim($branch[2]);
			} catch (\Throwable $e) {
				throw new VersionControlException('Could not determine the branch.', $e);
			}
		}

		return $this->localBranch;
	}

	/**
	 * Returns the commit id (7 hex digits) of the local head.
	 *
	 * @return string
	 *
	 * @throws VersionControlException
	 */
	public function getLocalHead(): string
	{
		if ($this->localHead === null) {
			try {
				$file = base_path('.git/refs/heads/' . $this->getLocalBranch());
				$commitID = file_get_contents($file);
				$this->localHead = self::trim($commitID);
			} catch (\Throwable $e) {
				throw new VersionControlException('Could not determine the head commit of current branch.', $e);
			}
		}

		return $this->localHead;
	}
}
