<?php

namespace App\Metadata;

use App\Configs;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Logs;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\JsonRequestFunctions;
use Config;
use Exception;

class GitHubFunctions
{
	private $head = false;
	private $branch = false;
	private $gitRequest;

	public function __construct(GitRequest $gitRequest)
	{
		$this->gitRequest = $gitRequest;
	}

	/**
	 * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
	 *
	 * @param $commit_id
	 *
	 * @return string
	 */
	private function trim($commit_id)
	{
		return trim(substr($commit_id, 0, 7));
	}

	/**
	 * Simple check of whether CI is running or not.
	 *
	 * @param $branch
	 *
	 * @return bool
	 */
	public function is_CI($branch)
	{
		return substr($branch, 0, 4) != 'ref:';
	}

	/**
	 * look at .git/HEAD and return the current branch.
	 * Return false if the file is not readable.
	 * Return master if it is CI.
	 *
	 * @return false|string
	 */
	public function get_current_branch()
	{
		if ($this->branch == false) {
			$this->branch = @file_get_contents(base_path('.git/HEAD'));
			// @codeCoverageIgnoreStart
			if ($this->branch != false) {
				// this is to handle CI where it actually checks a commit instead of a branch
				if ($this->is_CI($this->branch)) {
					// this is CI
					$this->branch = 'master';
				} else {
					// not CI
					$this->branch = explode('/', $this->branch,
						3)[2]; //separate out by the "/" in the string
				}
				$this->branch = trim($this->branch);
			} else {
				Logs::notice(__METHOD__, __LINE__,
					'Could not access: ' . base_path('.git/HEAD'));
			}
			// @codeCoverageIgnoreEnd
		}

		return $this->branch;
	}

	/**
	 * Return the current commit id (7 hex digits).
	 *
	 * @return false|string
	 */
	public function get_current_commit()
	{
		if ($this->head == false && $this->get_current_branch() != false) {
			$this->head = @file_get_contents(base_path('.git/refs/heads/'
				. $this->branch));
			if ($this->head != false) {
				$this->head = $this->trim($this->head);
			} else {
				// @codeCoverageIgnoreStart
				Logs::notice(__METHOD__, __LINE__,
					'Could not access: ' . base_path('.git/refs/heads/'
						. $this->branch));
				// @codeCoverageIgnoreEnd
			}
		}

		return $this->head;
	}

	/**
	 * return the list of the last 30 commits on the master branch.
	 *
	 * @param bool $cached
	 *
	 * @return bool|array
	 */
	public function get_commits(bool $cached = true)
	{
		return $this->gitRequest->get_json($cached);
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
	 * @return bool|int
	 *
	 * @throws NotInCacheException
	 * @throws NotMasterException
	 */
	public function count_behind(bool $cached = true)
	{
		$branch = $this->get_current_branch();
		if ($branch != 'master') {
			// @codeCoverageIgnoreStart
			throw new NotMasterException();
			// @codeCoverageIgnoreEnd
		}

		$head = $this->get_current_commit();

		$commits = $this->get_commits($cached);

		if ($commits == false) {
			throw new NotInCacheException();
		}

		$i = 0;
		while ($i < count($commits)) {
			if ($this->trim($commits[$i]->sha) == $head) {
				break;
			}
			$i++;
		}

		return ($i == count($commits)) ? false : $i;
	}

	/**
	 * return the commit id (7 hex digits) of the had if found.
	 *
	 * @return string
	 */
	public function get_github_head()
	{
		$commits = $this->get_commits();

		return ($commits != false) ? ' (' . $this->trim($commits[0]->sha) . ')'
			: '';
	}

	/**
	 * Return a string indicating whether we are up to date (used in Diagnostics).
	 *
	 * This function should not throw exceptions !
	 *
	 * @return string
	 */
	public function get_behind_text()
	{
		$branch = $this->get_current_branch();
		if ($branch != 'master') {
			// @codeCoverageIgnoreStart
			return ' - Branch is not master, cannot compare.';
			// @codeCoverageIgnoreEnd
		}

		try {
			$count = $this->count_behind(); // NotInCache or NotMaster
		} catch (Exception $e) {
			return ' - ' . $e->getMessage();
		}

		$last_update = $this->gitRequest->get_age_text();

		if ($count === 0) {
			return sprintf(' - Up to date (%s).', $last_update);
		}
		// @codeCoverageIgnoreStart
		if ($count != false) {
			return sprintf(' - %s commits behind master %s (%s)', $count,
				$this->get_github_head(), $last_update);
		}

		return ' - Probably more than 30 commits behind master';
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Return a string like 'commit number (branch)' or 'no git data found'.
	 *
	 * @return string
	 */
	public function get_info()
	{
		$branch = $this->get_current_branch();
		$head = $this->get_current_commit();
		if ($head == false || $branch == false) {
			// when going through CI, .git exists...
			// @codeCoverageIgnoreStart
			return 'No git data found. Probably installed from release or could not read .git';
			// @codeCoverageIgnoreEnd
		}

		return sprintf('%s (%s)', $head, $branch) . $this->get_behind_text();
	}

	/**
	 * Check if the repo is up to date, throw an exception if fails.
	 *
	 * @param bool $cached
	 *
	 * @return bool
	 *
	 * @throws NotMasterException
	 * @throws NotInCacheException
	 */
	public function is_up_to_date(bool $cached = true)
	{
		$count = $this->count_behind($cached);
		if ($count === 0) {
			return true;
		}

		// @codeCoverageIgnoreStart
		return false;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Simple check if git is usable or not.
	 *
	 * @return bool
	 */
	public function is_usable()
	{
		$usable = Helpers::hasFullPermissions(base_path('.git'));
		$branch = $this->get_current_branch();
		$usable &= Helpers::hasPermissions(base_path('.git/refs/heads/'
			. $branch));

		return $usable;
	}

	/**
	 * Check for updates (old).
	 *
	 * @param $return
	 */
	public function checkUpdates(&$return)
	{
		// add a setting to do this check only once per day ?
		if (Configs::get_value('check_for_updates', '0') == '1') {
			$json = new JsonRequestFunctions(Config::get('urls.update.json'));
			$json = $json->get_json();
			if ($json != false) {
				/* @noinspection PhpUndefinedFieldInspection */
				$return['update_json'] = $json->lychee->version;
				$return['update_available']
					= ((intval(Configs::get_value('version', '40000')))
					< $return['update_json']);
			}
		}
	}
}
