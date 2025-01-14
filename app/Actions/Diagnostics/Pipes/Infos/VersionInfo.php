<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticStringPipe;
use App\DTO\LycheeGitInfo;
use App\Enum\VersionChannelType;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;

/**
 * Which version of Lychee are we using?
 */
class VersionInfo implements DiagnosticStringPipe
{
	public function __construct(
		private InstalledVersion $installedVersion,
		public FileVersion $fileVersion,
		public GitHubVersion $gitHubFunctions,
		private Verify $verify,
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

		$data[] = Diagnostics::line($this->getVersionString() . ' (' . $channelName->value . '):', $lycheeInfoString);
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

	/**
	 * Retrieve the version string.
	 *
	 * SE for supporter edition
	 * Plus for premium edition
	 * The star marks a tampered installation
	 *
	 * @return string
	 */
	private function getVersionString(): string
	{
		$lychee_version = 'Lychee';
		$lychee_version .= match ($this->verify->get_status()) {
			Status::SUPPORTER_EDITION => ' SE',
			Status::PLUS_EDITION => ' Plus',
			default => '',
		};

		if (!$this->verify->validate()) {
			$lychee_version .= '*';
		}
		$lychee_version .= ' Version';

		return $lychee_version;
	}
}
