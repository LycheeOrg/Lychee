<?php

declare(strict_types=1);

namespace App\Actions\InstallUpdate\Pipes;

/**
 * Simple class to add a new line in the output.
 */
class Spacer extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$output[] = '';

		return $next($output);
	}
}