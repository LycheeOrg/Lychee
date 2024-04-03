<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Assets\Features;
use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\InstalledVersion;

/**
 * What kind of Lychee install we are looking at?
 * Composer? Dev? Release?
 */
class InstallTypeInfo implements DiagnosticPipe
{
	private InstalledVersion $installedVersion;

	public function __construct(InstalledVersion $installedVersion)
	{
		$this->installedVersion = $installedVersion;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$data[] = Diagnostics::line('composer install:', $this->installedVersion->isDev() ? 'dev' : '--no-dev');
		$data[] = Diagnostics::line('APP_ENV:', config('app.env')); // check if production
		$data[] = Diagnostics::line('APP_DEBUG:', config('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$data[] = Diagnostics::line('APP_URL:', config('app.url') !== 'http://localhost' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data[] = Diagnostics::line('APP_DIR:', config('app.dir_url') !== '' ? 'set' : 'default'); // Some people leave that value by default... It is now breaking their visual.
		$data[] = Diagnostics::line('LOG_VIEWER_ENABLED:', Features::when('log-viewer', 'true', 'false'));
		$data[] = Diagnostics::line('LIVEWIRE_ENABLED:', Features::when('livewire', 'true', 'false'));
		$data[] = '';

		return $next($data);
	}
}
