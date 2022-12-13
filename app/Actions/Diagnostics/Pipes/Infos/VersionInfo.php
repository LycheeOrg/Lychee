<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\LycheeGitInfo;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;

class VersionInfo implements DiagnosticPipe
{
	private InstalledVersion $installedVersion;

	public function __construct(
		InstalledVersion $installedVersion,
	) {
		$this->installedVersion = $installedVersion;
	}

	public function handle(array &$data, \Closure $next): array
	{
		if ($this->installedVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			$lycheeChannelName = 'release';

			$fileVersion = resolve(FileVersion::class);
			$fileVersion->hydrate(false, false);

			$lycheeInfoString = $fileVersion->getVersion()->toString();
		// @codeCoverageIgnoreEnd
		} else {
			$gitHubFunctions = resolve(GitHubVersion::class);
			$gitHubFunctions->hydrate();

			$lycheeChannelName = $gitHubFunctions->isRelease() ? 'tags' : 'git';

			if ($gitHubFunctions->localHead !== null) {
				$gitInfo = new LycheeGitInfo($gitHubFunctions);
				$lycheeInfoString = $gitInfo->toString();
			} else {
				$lycheeInfoString = 'No git data found.';
			}
		}

		$data[] = Diagnostics::line('Lychee Version (' . $lycheeChannelName . '):', $lycheeInfoString);
		$data[] = Diagnostics::line('DB Version:', $this->installedVersion->getVersion()->toString());
		$data[] = '';

		return $next($data);
	}
}
