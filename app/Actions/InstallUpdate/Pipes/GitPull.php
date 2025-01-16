<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use App\Facades\Helpers;
use App\Metadata\Versions\InstalledVersion;
use Illuminate\Support\Facades\Config;
use function Safe\exec;

class GitPull extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$installedVersion = resolve(InstalledVersion::class);
		if ($installedVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			return $next($output);
			// @codeCoverageIgnoreEnd
		}

		if (Helpers::isExecAvailable()) {
			$command = 'git pull --rebase ' . Config::get('urls.git.pull') . ' master 2>&1';
			exec($command, $output);

			return $next($output);
		}

		// @codeCoverageIgnoreStart
		return $output;
		// @codeCoverageIgnoreEnd
	}
}