<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;

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
				$data[] = DiagnosticData::error('PHP gd extension without jpeg support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gdVersion['PNG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without png support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (
				!$gdVersion['GIF Read Support'] ||
				!$gdVersion['GIF Create Support']
			) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without full gif support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gdVersion['WebP Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without WebP support', self::class);
				// @codeCoverageIgnoreEnd
			}
		}

		return $next($data);
	}
}
