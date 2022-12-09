<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Contracts\Versions\GitHubVersionControl;

class BranchCheck extends AbstractUpdaterPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$githubFunctions = resolve(GitHubVersionControl::class);
		$githubFunctions->hydrate(false);

		if ($githubFunctions->isMasterBranch()) {
			return $next($output);
		}

		$output[] = 'Branch is not Master';

		return $output;
	}
}