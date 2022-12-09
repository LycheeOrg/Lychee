<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\Contracts\Versions\LycheeVersionInterface;
use Illuminate\Support\Facades\Config;

class InstallTypeInfo implements DiagnosticPipe
{
	private LycheeVersionInterface $lycheeVersion;

	public function __construct(LycheeVersionInterface $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
	}

	public function handle(array &$data, \Closure $next): array
	{
		$data[] = Diagnostics::line('composer install:', $this->lycheeVersion->isDev() ? 'dev' : '--no-dev');
		$data[] = Diagnostics::line('APP_ENV:', Config::get('app.env')); // check if production
		$data[] = Diagnostics::line('APP_DEBUG:', Config::get('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$data[] = '';

		return $next($data);
	}
}
