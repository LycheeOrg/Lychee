<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\LycheeChannelInfo;
use App\Metadata\LycheeVersion;
use Closure;

class VersionInfo implements DiagnosticPipe
{
	private LycheeVersion $lycheeVersion;

	public function __construct(LycheeVersion $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
	}

	public function handle(array &$data, Closure $next): array
	{
		// Format Lychee Information
		$lycheeChannelInfo = $this->lycheeVersion->getLycheeChannelInfo();
		switch ($lycheeChannelInfo->channelType) {
			case LycheeChannelInfo::RELEASE_CHANNEL:
				$lycheeChannelName = 'release';
				$lycheeInfoString = $lycheeChannelInfo->releaseVersion->toString();
				break;
			case LycheeChannelInfo::GIT_CHANNEL:
				$lycheeChannelName = 'git';
				$lycheeInfoString = $lycheeChannelInfo->gitInfo !== null ? $lycheeChannelInfo->gitInfo->toString() : 'No git data found.';
				break;
			default:
				$lycheeChannelName = 'unknown';
				$lycheeInfoString = 'not available (this indicates an error)';
		}

		$data[] = Diagnostics::line('Lychee Version (' . $lycheeChannelName . '):', $lycheeInfoString);
		$data[] = Diagnostics::line('DB Version:', $this->lycheeVersion->getDBVersion()->toString());
		$data[] = '';

		return $next($data);
	}
}
