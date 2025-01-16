<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use Illuminate\Support\Facades\Artisan;

/**
 * Run the migration through the Artisan Facade.
 */
class ArtisanViewClear extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		Artisan::call('view:clear');
		$this->strToArray(Artisan::output(), $output);

		return $next($output);
	}
}