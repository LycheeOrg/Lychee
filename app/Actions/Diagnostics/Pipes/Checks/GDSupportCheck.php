<?php

declare(strict_types=1);

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

/**
 * Verify that GD support the correct images extensions.
 */
class GDSupportCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
			if (!$gdVersion['JPEG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = 'Error: PHP gd extension without jpeg support';
				// @codeCoverageIgnoreEnd
			}
			if (!$gdVersion['PNG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = 'Error: PHP gd extension without png support';
				// @codeCoverageIgnoreEnd
			}
			if (
				!$gdVersion['GIF Read Support'] ||
				!$gdVersion['GIF Create Support']
			) {
				// @codeCoverageIgnoreStart
				$data[] = 'Error: PHP gd extension without full gif support';
				// @codeCoverageIgnoreEnd
			}
			if (!$gdVersion['WebP Support']) {
				// @codeCoverageIgnoreStart
				$data[] = 'Error: PHP gd extension without WebP support';
				// @codeCoverageIgnoreEnd
			}
		}

		return $next($data);
	}
}
