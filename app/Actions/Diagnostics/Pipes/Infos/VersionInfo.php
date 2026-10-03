<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
			$lychee_info_string = $this->getGitInfo()?->toString() ?? 'No git data found.';
		}

		$data[] = Diagnostics::line($this->getVersionString() . ' (' . $channel_name->value . '):', $lychee_info_string);
		$data[] = Diagnostics::line('DB Version:', $this->installed_version->getVersion()->toString());
		$data[] = '';

		return $next($data);
	}

	/**
	 * Get channel name: no .git folder is a release, a detached HEAD is a tag, otherwise git.
	 * This also hydrates the git data used by {@link VersionInfo::getGitInfo()}.
	 */
	public function getChannelName(): VersionChannelType
	{
		if ($this->installed_version->isRelease()) {
			return VersionChannelType::RELEASE;
		}

		$this->github_functions->hydrate(with_remote: true, use_cache: true);

		return $this->github_functions->isDetached() ? VersionChannelType::TAG : VersionChannelType::GIT;
	}

	/**
	 * Describe the local git checkout, null if no commit could be read.
	 * Call {@link VersionInfo::getChannelName()} first.
	 */
	public function getGitInfo(): ?LycheeGitInfo
	{
		$head = $this->github_functions->local_head;
		if ($head === null) {
			return null;
		}

		if ($this->github_functions->isDetached()) {
			return new LycheeGitInfo(sprintf('%s (%s)', $this->file_version->getVersion()->toString(), $head), '');
		}

		return new LycheeGitInfo(
			sprintf('%s (%s)', $this->github_functions->local_branch, $head),
			$this->github_functions->getBehindTest(),
		);
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
			Status::PRO_EDITION => ' Pro',
			Status::SIGNATURE_EDITION => ' Signature',
			default => '',
		};

		if (!$this->verify->validate()) {
			// @codeCoverageIgnoreStart
			$lychee_version .= '*';
			// @codeCoverageIgnoreEnd
		}
		$lychee_version .= ' Version';

		return $lychee_version;
	}
}