<?php


namespace App\Metadata;


use App\Configs;
use App\Logs;

class GitHubFunctions
{

	private $commits = false;
	private $head = false;
	private $branch = false;



	private function trim($commit_id)
	{
		return trim(substr($commit_id, 0, 7));
	}



	/**
	 * Fetch an url with 1sec timout.
	 * @param $url
	 * @return bool|mixed
	 */
	private function get_json($url)
	{
		$opts = [
			'http' => [
				'method'  => 'GET',
				'timeout' => 1,
				'header'  => [
					'User-Agent: PHP'
				]
			]
		];
		$context = stream_context_create($opts);

		$json = @file_get_contents($url, false, $context);
		if ($json != false) {
			return json_decode($json);
		}
		Logs::notice(__FUNCTION__, __LINE__, "Could not access: ".$url);
		return false;
	}



	/**
	 * look at .git/HEAD and return the current branch
	 * @return false|string
	 */
	public function get_current_branch()
	{
		if ($this->branch == false) {
			$this->branch = @file_get_contents('../.git/HEAD');
			if ($this->branch != false) {
				$this->branch = explode("/", $this->branch, 3)[2]; //separate out by the "/" in the string
			}
			$this->branch = $this->trim($this->branch);
		}
		return $this->branch;
	}



	/**
	 * Return the current commit id (7 hex digits)
	 * @return false|string
	 */
	public function get_current_commit()
	{
		if ($this->head == false && $this->get_current_branch() != false) {
			$this->head = @file_get_contents(sprintf('../.git/refs/heads/%s', $this->branch));
			if ($this->head != false) {
				$this->head = $this->trim($this->head);
			}
		}
		return $this->head;
	}



	/**
	 * @return bool
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
	 * Return a string like 'commit number (branch)' or 'no git data found'
	 * @return string
	 */
	public function get_info()
	{
		$branch = $this->get_current_branch();
		$head = $this->get_current_commit();
		if ($head == false || $branch == false) {
			return 'No git data found. Probably installed from release.';
		}
		return sprintf('%s (%s)', $head, $branch).$this->get_behind_text();
	}



	/**
	 * Counter number of commits between current version and master/HEAD
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
	 * @return string
	 */
	public function get_github_head()
	{
		$commits = $this->get_commits();
		return ($commits != false) ? ' ('.$this->trim($commits[0]->sha).')' : '';
	}



	/**
	 * Check if current version is
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
		if ($count === 0)
		{
			return ' - Up to date.';
		}
		if ($count != false) {
			return ' - '.$count.' commits behind master'.$this->get_github_head();
		}

		return ' - Probably more than 30 commits behind master';
	}



	/**
	 * Check for updates
	 *
	 * @param $return
	 */
	public function checkUpdates(&$return)
	{
		// add a setting to do this check only once per day ?
		if (Configs::get_value('checkForUpdates', '0') == '1') {
			$json = $this->get_json('https://lycheeorg.github.io/update.json');
			if ($json != false) {
				$return['update_json'] = $json->lychee->version;
				$return['update_available'] = ((intval(Configs::get_value('version', '40000'))) < $return['update_json']);
			}
		}
	}
}