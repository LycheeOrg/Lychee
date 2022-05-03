<?php

namespace App\Metadata;

use App\DTO\LycheeChannelInfo;
use App\DTO\LycheeGitInfo;
use App\DTO\Version;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\VersionControlException;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;

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
	 * Return the info about the database.
	 *
	 * @return Version
	 */
	public function getDBVersion(): Version
	{
		return Version::createFromInt(
			Configs::get_value('version', '040000')
		);
	}

	/**
	 * Return the info about the version.md file.
	 *
	 * @return Version
	 *
	 * @throws LycheeInvalidArgumentException
	 * @throws FrameworkException
	 */
	public function getFileVersion(): Version
	{
		try {
			return Version::createFromString(
				file_get_contents(base_path('version.md'))
			);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

	/**
	 * Return the information with respect to Lychee.
	 *
	 * @return LycheeChannelInfo the version of lychee or null if no git data could be found
	 */
	public function getLycheeChannelInfo(): LycheeChannelInfo
	{
		if ($this->isRelease) {
			try {
				return LycheeChannelInfo::createReleaseInfo($this->getFileVersion());
			} catch (FrameworkException|LycheeInvalidArgumentException $e) {
				return LycheeChannelInfo::createReleaseInfo(null);
			}
		}

		try {
			$branch = $this->gitHubFunctions->getLocalBranch();
			$commit = $this->gitHubFunctions->getLocalHead();
			if (empty($commit) && empty($branch)) {
				return LycheeChannelInfo::createGitInfo(null);
			}

			return LycheeChannelInfo::createGitInfo(
				new LycheeGitInfo($branch, $commit, $this->gitHubFunctions->get_behind_text())
			);
		} catch (VersionControlException) {
			return LycheeChannelInfo::createGitInfo(null);
		}
	}
}
