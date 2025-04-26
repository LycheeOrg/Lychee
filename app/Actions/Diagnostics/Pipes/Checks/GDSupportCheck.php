<?php

/**
 * SPDX-License-Identifier: MIT
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
			$gd_version = gd_info();
			if (!$gd_version['JPEG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without jpeg support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gd_version['PNG Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without png support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (
				!$gd_version['GIF Read Support'] ||
				!$gd_version['GIF Create Support']
			) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without full gif support', self::class);
				// @codeCoverageIgnoreEnd
			}
			if (!$gd_version['WebP Support']) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::error('PHP gd extension without WebP support', self::class);
				// @codeCoverageIgnoreEnd
			}
		}

		return $next($data);
	}
}
