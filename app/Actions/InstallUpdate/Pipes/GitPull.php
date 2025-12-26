<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use App\Assets\CommandExecutor;
use App\Facades\Helpers;
use App\Metadata\Versions\InstalledVersion;

class GitPull extends AbstractUpdateInstallerPipe
{
	public function __construct(
		private readonly CommandExecutor $command_executor,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array $output, \Closure $next): array
	{
		$installed_version = resolve(InstalledVersion::class);
		if ($installed_version->isRelease()) {
			// @codeCoverageIgnoreStart
			return $next($output);
			// @codeCoverageIgnoreEnd
		}

		if (Helpers::isExecAvailable()) {
			$command = 'git pull --rebase ' . config('urls.git.pull') . ' master 2>&1';
			$this->command_executor->exec($command, $output);

			return $next($output);
		}

		// @codeCoverageIgnoreStart
		return $output;
		// @codeCoverageIgnoreEnd
	}
}