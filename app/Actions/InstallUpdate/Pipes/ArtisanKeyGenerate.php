<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use App\Exceptions\InstallationFailedException;
use Illuminate\Support\Facades\Artisan;

class ArtisanKeyGenerate extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		Artisan::call('key:generate', ['--force' => true]);

		$this->strToArray(Artisan::output(), $output);

		// Always false on CICD
		if (
			!str_contains(end($output), 'Application key set successfully') ||
			config('app.key') === null
		) {
			// @codeCoverageIgnoreStart
			$output[] = 'We could not generate the encryption key.';
			throw new InstallationFailedException('Could not generate encryption key');
			// @codeCoverageIgnoreEnd
		}

		return $next($output);
	}
}