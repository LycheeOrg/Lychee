<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Closure;

class GDSupportCheck implements DiagnosticPipe
{
	public function handle(array &$data, Closure $next): array
	{
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
			if (!$gdVersion['JPEG Support']) {
				$data[] = 'Error: PHP gd extension without jpeg support';
			}
			if (!$gdVersion['PNG Support']) {
				$data[] = 'Error: PHP gd extension without png support';
			}
			if (
				!$gdVersion['GIF Read Support']
				|| !$gdVersion['GIF Create Support']
			) {
				$data[] = 'Error: PHP gd extension without full gif support';
			}
		}

		return $next($data);
	}
}
