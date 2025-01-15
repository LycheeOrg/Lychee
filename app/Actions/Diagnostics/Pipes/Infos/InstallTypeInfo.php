<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Assets\Features;
use App\Contracts\DiagnosticStringPipe;
use App\Metadata\Versions\InstalledVersion;
use LycheeVerify\Verify;

/**
 * What kind of Lychee install we are looking at?
 * Composer? Dev? Release?
 */
class InstallTypeInfo implements DiagnosticStringPipe
{
	public function __construct(
		private InstalledVersion $installedVersion,
		private Verify $verify)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$data[] = Diagnostics::line('composer install:', ($this->installedVersion->isDev() ? 'dev' : '--no-dev') . ($this->verify->validate() ? '' : '*'));
		$data[] = Diagnostics::line('APP_ENV:', config('app.env')); // check if production
		$data[] = Diagnostics::line('APP_DEBUG:', config('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$data[] = Diagnostics::line('APP_URL:', config('app.url') !== 'http://localhost' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data[] = Diagnostics::line('APP_DIR:', config('app.dir_url') !== '' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data[] = Diagnostics::line('LOG_VIEWER_ENABLED:', Features::when('log-viewer', 'true', 'false'));
		$data[] = Diagnostics::line('VUEJS_ENABLED:', Features::when('vuejs', 'true', 'false'));
		$data[] = Diagnostics::line('PHOTO_PIPES:', Features::when('create-photo-via-pipes', 'true', 'false'));
		$data[] = '';

		return $next($data);
	}
}
