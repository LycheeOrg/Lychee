<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\LycheeVersion;

class BranchCheck extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$lycheeVersion = resolve(LycheeVersion::class);
		if ($lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			return $next($output);
			// @codeCoverageIgnoreEnd
		}

		$githubFunctions = resolve(GitHubVersion::class);
		$githubFunctions->hydrate(false);

		if ($githubFunctions->isMasterBranch()) {
			return $next($output);
		}

		$output[] = 'Branch is not ' . GitHubVersion::MASTER;

		return $output;
	}
}