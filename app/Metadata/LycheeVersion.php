<?php

namespace App\Metadata;

class LycheeVersion
{
	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var bool
	 */
	public $isRelease;

	public function __construct(GitHubFunctions $githubFunctions)
	{
		$this->gitHubFunctions = $githubFunctions;
		$this->isRelease = $this->fetchReleaseInfo();
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchReleaseInfo()
	{
		return !file_exists(base_path('.git'));
	}

	/**
	 * Return asked information.
	 *
	 * @return array
	 */
	public function get()
	{
		$versions = [];
		$versions['channel'] = $this->isRelease ? 'release' : 'git';
		$versions['LycheeFront'] = $this->getLycheeFrontVersion();
		$versions['Lychee'] = $this->getLycheeVersion();

		return $versions;
	}

	/**
	 * Format the version : number (commit id).
	 */
	public function format(array $info)
	{
		$ret = $info['version'];
		$ret .= (isset($info['commit']) ? ' (' . $info['commit'] . ')' : '');
		$ret .= $info['additional'] ?? '';

		return $ret;
	}

	/**
	 * Return the information with respect to LycheeFront.
	 *
	 * @return array
	 */
	private function getLycheeFrontVersion()
	{
		$json_lychee_front = @file_get_contents(base_path('public/dist/version.json'));
		// safety net in case the file does not exist...
		if (!$json_lychee_front) {
			// @codeCoverageIgnoreStart
			$json_lychee_front = ['version' => '-', 'commit' => '-'];
		// @codeCoverageIgnoreEnd
		} else {
			$json_lychee_front = json_decode($json_lychee_front, true);
		}

		return $json_lychee_front;
	}

	/**
	 * Return the information with respect to Lychee.
	 *
	 * @return array
	 */
	private function getLycheeVersion()
	{
		if ($this->isRelease) {
			// @codeCoverageIgnoreStart
			return ['version' => @file_get_contents(base_path('version.md'))];
			// @codeCoverageIgnoreEnd
		}

		$commit = $this->gitHubFunctions->get_current_commit();
		$branch = $this->gitHubFunctions->get_current_branch();
		if (!$commit && !$branch) {
			return ['version' => 'No git data found.'];
		}

		return ['version' => $branch, 'commit' => $commit, 'additional' => $this->gitHubFunctions->get_behind_text()];
	}
}