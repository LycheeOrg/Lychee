<?php

namespace App\Metadata;

use App\Exceptions\VersionControlException;
use App\Facades\Helpers;
use App\ModelFunctions\JsonRequestFunctions;
use App\Models\Configs;
use Illuminate\Support\Facades\Config;

class GitHubFunctions
{
	private GitRequest $gitRequest;
	protected string $head;
	protected string $branch;

	/**
	 * Base constructor.
	 *
	 * @param GitRequest $gitRequest
	 *
	 * @throws VersionControlException
	 */
	public function __construct(GitRequest $gitRequest)
	{
		$this->gitRequest = $gitRequest;
		$this->branch = $this->determine_current_branch();
		$this->head = $this->determine_current_commit();
	}

	/**
	 * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
	 *
	 * @param $commit_id
	 *
	 * @return string
	 */
	private function trim($commit_id): string
	{
		return trim(substr($commit_id, 0, 7));
	}

	/**
	 * Looks at .git/HEAD and returns the current branch.
	 *
	 * @return string the current branch
	 *
	 * @throws VersionControlException
	 */
	private function determine_current_branch(): string
	{
		try {
			$head_file = base_path('.git/HEAD');
			$branch = file_get_contents($head_file);
			if ($branch === false) {
				throw new \RuntimeException('`file_get_contents` returned `false`');
			}
			$branch = explode('/', $branch, 3);
			if ($branch === false) {
				throw new \RuntimeException('`explode` returned `false`');
			}

			return trim($branch[2]);
		} catch (\Throwable $e) {
			throw new VersionControlException('Could not determine the branch', $e);
		}
	}

	/**
	 * Determines the head commit id (7 hex digits) of the current branch.
	 *
	 * @return string
	 *
	 * @throws VersionControlException
	 */
	private function determine_current_commit(): string
	{
		try {
			$file = base_path('.git/refs/heads/' . $this->branch);
			$commitID = file_get_contents($file);
			if ($commitID === false) {
				throw new \RuntimeException('`file_get_contents` returned `false`');
			}
		} catch (\Throwable $e) {
			throw new VersionControlException('Could not determine the head commit of current branch', $e);
		}

		return $this->trim($commitID);
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
	public function count_behind(bool $cached = true): int
	{
		if ($this->branch !== 'master') {
			throw new VersionControlException('Branch is not master, cannot compare');
		}

		$commits = $this->get_commits($cached);

		$i = 0;
		while ($i < count($commits)) {
			if ($this->trim($commits[$i]->sha) == $this->head) {
				break;
			}
			$i++;
		}

		if ($i === count($commits)) {
			throw new VersionControlException('More than 30 commits behind');
		}

		return $i;
	}

	/**
	 * return the commit id (7 hex digits) of the head if found.
	 *
	 * @return string
	 */
	public function get_github_head(): string
	{
		try {
			$commits = $this->get_commits();

			return ' (' . $this->trim($commits[0]->sha) . ')';
		} catch (VersionControlException $e) {
			return '';
		}
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
		} catch (VersionControlException $e) {
			return ' - ' . $e->getMessage();
		}

		$last_update = $this->gitRequest->get_age_text();

		if ($count === 0) {
			return sprintf(' - Up to date (%s).', $last_update);
		} else {
			return sprintf(
				' - %s commits behind master %s (%s)',
				$count,
				$this->get_github_head(),
				$last_update
			);
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
		return Helpers::hasFullPermissions(base_path('.git')) && Helpers::hasPermissions(base_path('.git/refs/heads/' . $this->branch));
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
		if (Configs::get_value('check_for_updates', '0') == '0') {
			return [];
		}

		try {
			$json = new JsonRequestFunctions(Config::get('urls.update.json'));
			$json = $json->get_json();

			return [
				'update_json' => intval($json->lychee->version),
				'update_available' => (
					(intval(Configs::get_value('version', '40000'))) <
					$json->lychee->version
				),
			];
		} catch (\Throwable $e) {
			throw new VersionControlException('Could not check for updates', $e);
		}
	}

	/**
	 * Return true if the current branch is master.
	 * This is used to avoid running git pulls on development branches during tests.
	 *
	 * @return bool
	 */
	public function is_master_branch(): bool
	{
		return $this->branch === 'master';
	}

	public function getBranch(): string
	{
		return $this->branch;
	}

	public function getHead(): string
	{
		return $this->head;
	}
}
