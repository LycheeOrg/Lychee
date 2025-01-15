<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;

class BranchCheck extends AbstractUpdateInstallerPipe
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

		$githubFunctions = resolve(GitHubVersion::class);
		$githubFunctions->hydrate(false);

		if ($githubFunctions->isMasterBranch()) {
			return $next($output);
		}

		// @codeCoverageIgnoreStart
		$output[] = 'Branch is not ' . GitHubVersion::MASTER;

		return $output;
		// @codeCoverageIgnoreEnd
	}
}