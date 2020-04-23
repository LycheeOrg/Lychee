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

	/**
	 * true if phpunit is present in vendor/bin/
	 * We use this to dertermine if composer install or composer install --no-dev was used.
	 *
	 * @var bool
	 */
	public $phpUnit;

	/**
	 * Base constructor.
	 *
	 * @param GitHubFunctions
	 */
	public function __construct(GitHubFunctions $githubFunctions)
	{
		$this->gitHubFunctions = $githubFunctions;
		$this->isRelease = $this->fetchReleaseInfo();
		$this->phpUnit = $this->fetchComposerInfo();
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
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchComposerInfo()
	{
		return file_exists(base_path('vendor/bin/phpunit'));
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
		$versions['composer'] = $this->phpUnit ? 'dev' : '--no-dev';
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
	 * Return the information with respect to Lychee.
	 *
	 * @return array
	 */
	private function getLycheeVersion()
	{
		if ($this->isRelease) {
			// @codeCoverageIgnoreStart
			return ['version' => rtrim(@file_get_contents(base_path('version.md')))];
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