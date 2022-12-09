<?php

namespace App\Metadata\Versions;

use App\Contracts\Versions\GitHubVersionControl;
use App\Contracts\Versions\HasVersion;
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

class LycheeVersion implements LycheeVersionInterface, HasVersion
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
		$this->isRelease = !File::exists(base_path('.git'));
		$this->phpUnit = File::exists(base_path('vendor/bin/phpunit'));
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
	public function getVersion(): Version
	{
		return Version::createFromInt(Configs::getValueAsInt('version'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLycheeChannelInfo(): LycheeChannelInfo
	{
		$fileVersion = resolve(FileVersion::class);
		$fileVersion->hydrate(false, false);

		if ($this->isRelease) {
			try {
				return LycheeChannelInfo::createReleaseInfo($fileVersion->getVersion());
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
}
