<?php

/**
 * SPDX-License-Identifier: MIT
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
	public function handle(array $output, \Closure $next): array
	{
		$installed_version = resolve(InstalledVersion::class);
		if ($installed_version->isRelease()) {
			// @codeCoverageIgnoreStart
			return $next($output);
			// @codeCoverageIgnoreEnd
		}

		$github_functions = resolve(GitHubVersion::class);
		$github_functions->hydrate(false);

		if ($github_functions->isMasterBranch()) {
			return $next($output);
		}

		// @codeCoverageIgnoreStart
		$output[] = 'Branch is not ' . GitHubVersion::MASTER;

		return $output;
		// @codeCoverageIgnoreEnd
	}
}