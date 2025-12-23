<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Assets\Features;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticDTO;
use App\Metadata\Versions\InstalledVersion;
use LycheeVerify\Verify;

/**
 * What kind of Lychee install we are looking at?
 * Composer? Dev? Release?
 */
class InstallTypeInfo implements DiagnosticPipe
{
	public function __construct(
		private InstalledVersion $installed_version,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		$data->data[] = Diagnostics::line('composer install:', ($this->installed_version->isDev() ? 'dev' : '--no-dev') . (($data->verify instanceof Verify && $data->verify->validate()) ? '' : '*'));
		$data->data[] = Diagnostics::line('APP_ENV:', config('app.env')); // check if production
		$data->data[] = Diagnostics::line('APP_DEBUG:', config('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$data->data[] = Diagnostics::line('APP_URL:', config('app.url') !== 'http://localhost' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data->data[] = Diagnostics::line('APP_DIR:', config('app.dir_url') !== '' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data->data[] = Diagnostics::line('LOG_VIEWER_ENABLED:', Features::when('log-viewer', 'true', 'false'));
		$data->data[] = '';

		return $next($data);
	}
}
