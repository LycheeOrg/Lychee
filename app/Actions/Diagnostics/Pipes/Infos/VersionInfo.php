<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\LycheeGitInfo;
use App\Enum\VersionChannelType;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;

/**
 * Which version of Lychee are we using?
 */
class VersionInfo implements DiagnosticPipe
{
	public function __construct(
		private InstalledVersion $installedVersion,
		public FileVersion $fileVersion,
		public GitHubVersion $gitHubFunctions,
	) {
		$this->fileVersion->hydrate(withRemote: false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		/** @var VersionChannelType $channelName */
		$channelName = $this->getChannelName();
		$lycheeInfoString = $this->fileVersion->getVersion()->toString();

		if ($channelName !== VersionChannelType::RELEASE) {
			if ($this->gitHubFunctions->localHead !== null) {
				$gitInfo = new LycheeGitInfo($this->gitHubFunctions);
				$lycheeInfoString = $gitInfo->toString();
			} else {
				// @codeCoverageIgnoreStart
				$lycheeInfoString = 'No git data found.';
				// @codeCoverageIgnoreEnd
			}
		}

		$data[] = Diagnostics::line('Lychee Version (' . $channelName->value . '):', $lycheeInfoString);
		$data[] = Diagnostics::line('DB Version:', $this->installedVersion->getVersion()->toString());
		$data[] = '';

		return $next($data);
	}

	/**
	 * Get channel name.
	 *
	 * @return VersionChannelType
	 */
	public function getChannelName()
	{
		$lycheeChannelName = VersionChannelType::RELEASE;

		if (!$this->installedVersion->isRelease()) {
			$this->gitHubFunctions->hydrate(withRemote: true, useCache: true);
			$lycheeChannelName = $this->gitHubFunctions->isRelease() ? VersionChannelType::TAG : VersionChannelType::GIT;
		}

		return $lycheeChannelName;
	}
}
