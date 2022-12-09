<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Metadata\Versions\GitHubVersion;

class BranchCheck extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$githubFunctions = resolve(GitHubVersion::class);
		$githubFunctions->hydrate(false);

		if ($githubFunctions->isMasterBranch()) {
			return $next($output);
		}

		$output[] = 'Branch is not Master';

		return $output;
	}
}