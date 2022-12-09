<?php

namespace App\Metadata\Versions;

use App\Contracts\Versions\GitHubVersionControl;
use App\Contracts\Versions\LycheeVersionInterface;
use App\DTO\LycheeChannelInfo;
use App\DTO\LycheeGitInfo;
use App\DTO\Version;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\VersionControlException;
use App\Models\Configs;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class LycheeVersion implements LycheeVersionInterface
{
	private bool $isRelease;

	/**
	 * true if phpunit is present in vendor/bin/
	 * We use this to determine if composer install or composer install --no-dev was used.
	 */
	private bool $phpUnit;

	/**
	 * Base constructor.
	 */
	public function __construct()
	{
		$this->isRelease = $this->fetchReleaseInfo();
		$this->phpUnit = $this->fetchComposerInfo();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isRelease(): bool
	{
		return $this->isRelease;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDev(): bool
	{
		return $this->phpUnit;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getDBVersion(): Version
	{
		return Version::createFromInt(Configs::getValueAsInt('version'));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws FileNotFoundException
	 */
	public function getFileVersion(): Version
	{
		return Version::createFromString(
			File::get(base_path('version.md'))
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLycheeChannelInfo(): LycheeChannelInfo
	{
		if ($this->isRelease) {
			try {
				return LycheeChannelInfo::createReleaseInfo($this->getFileVersion());
			} catch (FileNotFoundException|ConfigurationKeyMissingException|LycheeInvalidArgumentException $e) {
				return LycheeChannelInfo::createReleaseInfo(null);
			}
		}

		try {
			$gitHubFunctions = resolve(GitHubVersionControl::class);
			$gitHubFunctions->hydrate();

			$branch = $gitHubFunctions->localBranch;
			$commit = $gitHubFunctions->localHead;

			if ($commit === null || $branch === null) {
				return LycheeChannelInfo::createGitInfo(null);
			}

			return LycheeChannelInfo::createGitInfo(new LycheeGitInfo($gitHubFunctions));
		} catch (VersionControlException) {
			return LycheeChannelInfo::createGitInfo(null);
		}
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchReleaseInfo(): bool
	{
		return !File::exists(base_path('.git'));
	}

	/**
	 * Returns true if we are using the --dev mode,
	 * Returns false if we are using the --no-dev mode.
	 */
	private function fetchComposerInfo(): bool
	{
		return File::exists(base_path('vendor/bin/phpunit'));
	}
}
