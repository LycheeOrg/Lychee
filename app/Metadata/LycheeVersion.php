<?php

namespace App\Metadata;

use App\Models\Configs;

class LycheeVersion
{
	private GitHubFunctions $gitHubFunctions;

	public bool $isRelease;

	/**
	 * true if phpunit is present in vendor/bin/
	 * We use this to determine if composer install or composer install --no-dev was used.
	 */
	public bool $phpUnit;

	/**
	 * Base constructor.
	 *
	 * @param GitHubFunctions
	 */
	public function __construct(GitHubFunctions $gitHubFunctions)
	{
		$this->gitHubFunctions = $gitHubFunctions;
		$this->isRelease = $this->fetchReleaseInfo();
		$this->phpUnit = $this->fetchComposerInfo();
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchReleaseInfo(): bool
	{
		return !file_exists(base_path('.git'));
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchComposerInfo(): bool
	{
		return file_exists(base_path('vendor/bin/phpunit'));
	}

	/**
	 * Return asked information.
	 *
	 * @return array{channel: string, composer: string, DB: }
	 */
	public function get(): array
	{
		$versions = [];
		$versions['channel'] = $this->isRelease ? 'release' : 'git';
		$versions['composer'] = $this->phpUnit ? 'dev' : '--no-dev';
		$versions['DB'] = $this->getDBVersion();
		$versions['Lychee'] = $this->getLycheeVersion();

		return $versions;
	}

	/**
	 * Format the version : number (commit id).
	 */
	public function format(array $info): string
	{
		$ret = $info['version'];
		$ret .= (isset($info['commit']) ? ' (' . $info['commit'] . ')' : '');
		$ret .= $info['additional'] ?? '';

		return $ret;
	}

	/**
	 * @param string $version in the shape of xx.yy.zz
	 *
	 * @return string xx.yy.zz
	 */
	public function format_version(string $version): string
	{
		return implode('.', array_map('intval', str_split($version, 2)));
	}

	/**
	 * Return the info about the database.
	 *
	 * @return array{version: string}
	 */
	public function getDBVersion(): array
	{
		return ['version' => $this->format_version(Configs::get_value('version', '040000'))];
	}

	/**
	 * Return the info about the version.md file.
	 *
	 * @return array{version: string}
	 */
	public function getFileVersion(): array
	{
		return ['version' => rtrim(@file_get_contents(base_path('version.md')))];
	}

	/**
	 * Return the information with respect to Lychee.
	 *
	 * @return array{version: string, commit: string, additional: string}
	 */
	private function getLycheeVersion(): array
	{
		if ($this->isRelease) {
			return $this->getFileVersion();
		}

		$branch = $this->gitHubFunctions->getBranch();
		$commit = $this->gitHubFunctions->getHead();
		if (empty($commit) && empty($branch)) {
			return ['version' => 'No git data found.'];
		}

		return ['version' => $branch, 'commit' => $commit, 'additional' => $this->gitHubFunctions->get_behind_text()];
	}
}
