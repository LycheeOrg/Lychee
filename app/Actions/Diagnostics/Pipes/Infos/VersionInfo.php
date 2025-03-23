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
		private InstalledVersion $installed_version,
		public FileVersion $file_version,
		public GitHubVersion $github_functions,
		private Verify $verify,
	) {
		$this->file_version->hydrate(with_remote: false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		/** @var VersionChannelType $channel_name */
		$channel_name = $this->getChannelName();
		$lychee_info_string = $this->file_version->getVersion()->toString();

		if ($channel_name !== VersionChannelType::RELEASE) {
			if ($this->github_functions->local_head !== null) {
				$git_info = new LycheeGitInfo($this->github_functions);
				$lychee_info_string = $git_info->toString();
			} else {
				// @codeCoverageIgnoreStart
				$lychee_info_string = 'No git data found.';
				// @codeCoverageIgnoreEnd
			}
		}

		$data[] = Diagnostics::line($this->getVersionString() . ' (' . $channel_name->value . '):', $lychee_info_string);
		$data[] = Diagnostics::line('DB Version:', $this->installed_version->getVersion()->toString());
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
		$lychee_channel_name = VersionChannelType::RELEASE;

		if (!$this->installed_version->isRelease()) {
			$this->github_functions->hydrate(with_remote: true, use_cache: true);
			$lychee_channel_name = $this->github_functions->isRelease() ? VersionChannelType::TAG : VersionChannelType::GIT;
		}

		return $lychee_channel_name;
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