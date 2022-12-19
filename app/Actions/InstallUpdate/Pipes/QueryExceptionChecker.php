<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Exceptions\InstallationFailedException;

/**
 * Check that there was no Query exception in the output.
 */
class QueryExceptionChecker extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		/*
		 * We check there is no "QueryException" in the output (just to be sure).
		 */
		foreach ($output as $line) {
			if (str_contains($line, 'QueryException')) {
				throw new InstallationFailedException('DB migration failed: ' . $line);
			}
		}

		return $next($output);
	}
}