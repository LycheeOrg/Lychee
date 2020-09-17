<?php

namespace App\Metadata;

use App;
use App\Assets\Helpers;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\ModelFunctions\JsonRequestFunctions;
use App\Models\Configs;
use App\Models\Logs;
use Config;
use Exception;

class GitHubFunctions
{
	/**
	 * @var string
	 */
	public $head;

	/**
	 * @var string
	 */
	public $branch;

	/**
	 * @var GitRequest
	 */
	private $gitRequest;

	/**
	 * Base constructor.
	 *
	 * @param GitRequest $gitRequest
	 */
	public function __construct(GitRequest $gitRequest)
	{
		$this->gitRequest = $gitRequest;
		try {
			$this->branch = $this->get_current_branch();
			$this->head = $this->get_current_commit();
			// @codeCoverageIgnoreStart
			// when testing on master branch this is not covered.
		} catch (Exception $e) {
			$this->branch = false;
			$this->head = false;
			try {
				Logs::notice(__METHOD__, __LINE__, $e->getMessage());
			} catch (Exception $e) {
				// Composer stuff.
			}
		}
		// @codeCoverageIgnoreEnd
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
	 * look at .git/HEAD and return the current branch.
	 * Return false if the file is not readable.
	 * Return master if it is CI.
	 *
	 * @return false|string
	 */
	public function get_current_branch()
	{
		if (App::runningUnitTests()) {
			return 'master';
		}

		// @codeCoverageIgnoreStart
		$head_file = base_path('.git/HEAD');
		$branch_ = file_get_contents($head_file);
		//separate out by the "/" in the string
		$branch_ = explode('/', $branch_, 3);

		return trim($branch_[2]);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Return the current commit id (7 hex digits).
	 *
	 * @return false|string
	 */
	public function get_current_commit()
	{
		$file = base_path('.git/refs/heads/' . $this->branch);
		$head_ = file_get_contents($file);

		return $this->trim($head_);
	}

	/**
	 * return the list of the last 30 commits on the master branch.
	 *
	 * @param bool $cached
	 *
	 * @return bool|array
	 *
	 * @throws NotInCacheException
	 */
	private function get_commits(bool $cached = true)
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
		if ($this->branch != 'master') {
			// @codeCoverageIgnoreStart
			throw new NotMasterException();
			// @codeCoverageIgnoreEnd
		}

		$commits = $this->get_commits($cached);

		$i = 0;
		while ($i < count($commits)) {
			if ($this->trim($commits[$i]->sha) == $this->head) {
				break;
			}
			// @codeCoverageIgnoreStart
			// when testing on master branch this is not covered: we are up to date.
			$i++;
			// @codeCoverageIgnoreEnd
		}

		return ($i == count($commits)) ? false : $i;
	}

	/**
	 * return the commit id (7 hex digits) of the had if found.
	 *
	 * @return string
	 */
	// @codeCoverageIgnoreStart
	public function get_github_head()
	{
		try {
			$commits = $this->get_commits();

			return ' (' . $this->trim($commits[0]->sha) . ')';
		} catch (Exception $e) {
			return '';
		}
	}

	// @codeCoverageIgnoreEnd

	/**
	 * Return a string indicating whether we are up to date (used in Diagnostics).
	 *
	 * This function should not throw exceptions !
	 *
	 * @return string
	 */
	public function get_behind_text()
	{
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
			return sprintf(
				' - %s commits behind master %s (%s)',
				$count,
				$this->get_github_head(),
				$last_update
			);
		}

		return ' - Probably more than 30 commits behind master';
		// @codeCoverageIgnoreEnd
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
	public function has_permissions()
	{
		if (!$this->branch) {
			// @codeCoverageIgnoreStart
			return false;
		// @codeCoverageIgnoreEnd
		} else {
			return Helpers::hasFullPermissions(base_path('.git')) && Helpers::hasPermissions(base_path('.git/refs/heads/' . $this->branch));
		}
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
