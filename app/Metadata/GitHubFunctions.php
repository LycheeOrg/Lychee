<?php

namespace App\Metadata;

use App;
use App\Configs;
use App\Logs;
use Cache;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Psr\SimpleCache\InvalidArgumentException;

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
		$json = Cache::get($url);
		if ($json == null) {
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

			/** @var string|false $json */
			$json = @file_get_contents($url, false, $context);

			if ($json != false) {
				$days = intval(Configs::get_value('update_check_every_days', '3'), 10);
				try {
					Cache::set($url, $json, now()->addDays($days));
					Logs::notice(__METHOD__, __LINE__, 'Setting cache for ' . $url . ' = ' . now()->addDays($days));
					Cache::set($url . '_age', now(), now()->addDays($days));
					Logs::notice(__METHOD__, __LINE__, 'Setting cache for ' . $url . '_age = ' . now()->addDays($days));
				} catch (InvalidArgumentException $e) {
					Logs::error(__METHOD__, __LINE__, 'Could not set in the cache');
				}

				return json_decode($json);
			}
			// @codeCoverageIgnoreStart
			Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . $url);

			return false;
			// @codeCoverageIgnoreEnd
		}
		Logs::notice(__METHOD__, __LINE__, 'cache has: ' . $url);

		return json_decode($json);
	}

	/**
	 * return the date of the last request to api.github.com/repos/LycheeOrg/Lychee-Laravel/commits.
	 *
	 * @return Carbon|null
	 */
	public function get_update_age()
	{
		return Cache::get(Config::get('urls.update.git') . '_age');
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
			// @codeCoverageIgnoreStart
			if ($this->branch != false) {
				// this is to handle CI where it actually checks a commit instead of a branch
				if (substr($this->branch, 0, 4) == 'ref:') {
					// not CI
					$this->branch = explode('/', $this->branch, 3)[2]; //separate out by the "/" in the string
				} else {
					// this is CI
					$this->branch = 'master';
					$this->CI_commit = $this->branch;
				}
			} else {
				Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . base_path('.git/HEAD'));
			}
			// @codeCoverageIgnoreEnd
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
				// @codeCoverageIgnoreStart
				Logs::notice(__METHOD__, __LINE__, 'Could not access: ' . base_path('.git/refs/heads/' . $this->branch));
				if ($this->CI_commit != false) {
					$this->head = $this->trim($this->CI_commit);
				}
				// @codeCoverageIgnoreEnd
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
			$this->commits = $this->get_json(Config::get('urls.update.git'));
		}

		return $this->commits;
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
			// @codeCoverageIgnoreStart
			return ' - Branch is not master, cannot compare.';
			// @codeCoverageIgnoreEnd
		}

		if ($this->get_commits() == false) {
			// @codeCoverageIgnoreStart
			return ' - Check for update failed.';
			// @codeCoverageIgnoreEnd
		}

		$count = $this->count_behind();

		/** @var Carbon|null $last_update */
		$last_update = $this->get_update_age();
		if (!$last_update) {
			$last = 'unknown';
			$end = '';
		} else {
			$last = now()->diffInDays($last_update);
			$end = $last > 0 ? ' days' : '';
			$last = ($last == 0 && $end = ' hours') ? now()->diffInHours($last_update) : $last;
			$last = ($last == 0 && $end = ' minutes') ? now()->diffInMinutes($last_update) : $last;
			$last = ($last == 0 && $end = ' seconds') ? now()->diffInSeconds($last_update) : $last;
			$end = $end . ' ago';
		}

		if ($count === 0) {
			return sprintf(' - Up to date (%s).', $last . $end);
		}
		// @codeCoverageIgnoreStart
		if ($count != false) {
			return sprintf(' - %s commits behind master %s (%s)', $count, $this->get_github_head(), $last);
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
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function is_up_to_date()
	{
		$branch = $this->get_current_branch();
		if ($branch != 'master') {
			// @codeCoverageIgnoreStart
			throw new Exception('Branch is not master, cannot compare.');
			// @codeCoverageIgnoreStart
		}

		if ($this->get_commits() == false) {
			// @codeCoverageIgnoreStart
			throw new Exception('Check for update failed.');
			// @codeCoverageIgnoreEnd
		}

		$count = $this->count_behind();
		if ($count === 0) {
			return true;
		}
		// @codeCoverageIgnoreStart
		return false;
		// @codeCoverageIgnoreEnd
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
			$json = $this->get_json(Config::get('urls.update.json'));
			if ($json != false) {
				$return['update_json'] = $json->lychee->version;
				$return['update_available'] = ((intval(Configs::get_value('version', '40000'))) < $return['update_json']);
			}
		}
	}
}
