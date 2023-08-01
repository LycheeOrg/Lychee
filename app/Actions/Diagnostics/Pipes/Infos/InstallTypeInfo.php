<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\InstalledVersion;
use Illuminate\Support\Facades\Config;

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
		$data[] = Diagnostics::line('APP_ENV:', Config::get('app.env')); // check if production
		$data[] = Diagnostics::line('APP_DEBUG:', Config::get('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$data[] = '';

		return $next($data);
	}
}
