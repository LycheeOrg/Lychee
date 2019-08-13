<?php

namespace App\Metadata;

use App;
use App\Configs;
use App\Logs;
use Exception;

class GitHubFunctions
{
	private $commits = false;
	private $head = false;
	private $branch = false;
	private $CI_commit = false;

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
	 * Fetch an url with 1sec timout.
	 *
	 * @param $url
	 *
	 * @return bool|mixed
	 */
	private function get_json($url)
	{
		$opts = [
			'http' => [
				'method' => 'GET',
				'timeout' => 1,
				'header' => [
					'User-Agent: PHP',
				],
			],
		];
		$context = stream_context_create($opts);

		$json = @file_get_contents($url, false, $context);
		if ($json != false) {
			return json_decode($json);
		}
		Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . $url);

		return false;
	}

	/**
	 * look at .git/HEAD and return the current branch.
	 *
	 * @return false|string
	 */
	public function get_current_branch()
	{
		if ($this->branch == false) {
			$this->branch = @file_get_contents(base_path('.git/HEAD'));
			if ($this->branch != false) {
				// this is to handle CI where it actually checks a commit instead of a branch
				if (substr($this->branch, 0, 4) == 'refs:') {
					$this->branch = explode('/', $this->branch, 3)[2]; //separate out by the "/" in the string
				} else {
					$this->branch = 'master';
					$this->CI_commit = $this->branch;
				}
			} else {
				Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . base_path('.git/HEAD'));
			}
			$this->branch = trim($this->branch);
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
			$this->head = @file_get_contents(base_path('.git/refs/heads/' . $this->branch));
			if ($this->head != false) {
				$this->head = $this->trim($this->head);
			} else {
				Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . base_path('.git/refs/heads/' . $this->branch));
				if ($this->CI_commit != false) {
					$this->head = $this->trim($this->CI_commit);
				}
			}
		}

		return $this->head;
	}

	/**
	 * return the list of the last 30 commits on the master branch.
	 *
	 * @return bool|array
	 */
	public function get_commits()
	{
		if (!$this->commits) {
			$branch = $this->get_current_branch();
			if ($branch != 'master') {
				return false;
			}

			$head = $this->get_current_commit();
			if ($head == false) {
				return false;
			}

			// get 30 last commits.
			$this->commits = $this->get_json('http://api.github.com/repos/LycheeOrg/Lychee-Laravel/commits');
		}

		return $this->commits;
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
			return 'No git data found. Probably installed from release or could not read .git';
		}

		return sprintf('%s (%s)', $head, $branch) . $this->get_behind_text();
	}

	/**
	 * Count the number of commits between current version and master/HEAD.
	 *
	 * @return bool|int
	 */
	public function count_behind()
	{
		$head = $this->get_current_commit();

		/** @var bool|array $commits */
		$commits = $this->get_commits();
		if ($commits == false) {
			return false;
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

		return ($commits != false) ? ' (' . $this->trim($commits[0]->sha) . ')' : '';
	}

	/**
	 * Return a string indicating whether we are up to date (used in Diagnostics).
	 *
	 * @return string
	 */
	public function get_behind_text()
	{
		$branch = $this->get_current_branch();
		if ($branch != 'master') {
			return ' - Branch is not master, cannot compare.';
		}

		if ($this->get_commits() == false) {
			return ' - Check for update failed.';
		}

		$count = $this->count_behind();
		if ($count === 0) {
			return ' - Up to date.';
		}
		if ($count != false) {
			return ' - ' . $count . ' commits behind master' . $this->get_github_head();
		}

		return ' - Probably more than 30 commits behind master';
	}

	/**
	 * Check if the repo is up to date, throw an exception if fails.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function is_up_to_date()
	{
		$branch = $this->get_current_branch();
		if ($branch != 'master') {
			throw new Exception('Branch is not master, cannot compare.');
		}

		if ($this->get_commits() == false) {
			throw new Exception('Check for update failed.');
		}

		$count = $this->count_behind();
		if ($count === 0) {
			return true;
		}

		return false;
	}

	/**
	 * Check for updates.
	 *
	 * @param $return
	 */
	public function checkUpdates(&$return)
	{
		// add a setting to do this check only once per day ?
		if (Configs::get_value('check_for_updates', '0') == '1') {
			$json = $this->get_json('https://lycheeorg.github.io/update.json');
			if ($json != false) {
				$return['update_json'] = $json->lychee->version;
				$return['update_available'] = ((intval(Configs::get_value('version', '40000'))) < $return['update_json']);
			}
		}
	}
}
