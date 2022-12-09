<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\LycheeGitInfo;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\LycheeVersion;

class VersionInfo implements DiagnosticPipe
{
	private LycheeVersion $lycheeVersion;

	public function __construct(
		LycheeVersion $lycheeVersion,
	) {
		$this->lycheeVersion = $lycheeVersion;
	}

	public function handle(array &$data, \Closure $next): array
	{
		if ($this->lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			$lycheeChannelName = 'release';

			$fileVersion = resolve(FileVersion::class);
			$fileVersion->hydrate(false, false);

			$lycheeInfoString = $fileVersion->getVersion()->toString();
		// @codeCoverageIgnoreEnd
		} else {
			$lycheeChannelName = 'git';

			$gitHubFunctions = resolve(GitHubVersion::class);
			$gitHubFunctions->hydrate();

			if ($gitHubFunctions->localHead !== null && $gitHubFunctions->localBranch !== null) {
				$gitInfo = new LycheeGitInfo($gitHubFunctions);
				$lycheeInfoString = $gitInfo->toString();
			} else {
				$lycheeInfoString = 'No git data found.';
			}
		}

		$data[] = Diagnostics::line('Lychee Version (' . $lycheeChannelName . '):', $lycheeInfoString);
		$data[] = Diagnostics::line('DB Version:', $this->lycheeVersion->getVersion()->toString());
		$data[] = '';

		return $next($data);
	}
}
