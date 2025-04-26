<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticStringPipe;
use App\Facades\Helpers;
use App\Models\Configs;

/**
 * Info on what image processing we have available.
 */
class ExtensionsInfo implements DiagnosticStringPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		// Load settings
		$settings = Configs::get();

		// About Imagick version
		$imagick = extension_loaded('imagick');
		if ($imagick === true) {
			/** @disregard P1009 */
			$imagick_version = \Imagick::getVersion();
		} else {
			// @codeCoverageIgnoreStart
			$imagick = '-';
			// @codeCoverageIgnoreEnd
		}
		if (!isset($imagick_version, $imagick_version['versionNumber'])) {
			// @codeCoverageIgnoreStart
			$imagick_version = '-';
		// @codeCoverageIgnoreEnd
		} else {
			$imagick_version = $imagick_version['versionNumber'];
		}

		// About GD version
		if (function_exists('gd_info')) {
			$gd_version = gd_info();
		} else {
			// @codeCoverageIgnoreStart
			$gd_version = ['GD Version' => '-'];
			// @codeCoverageIgnoreEnd
		}

		$data[] = Diagnostics::line('exec() Available:', Helpers::isExecAvailable() ? 'yes' : 'no');
		$data[] = Diagnostics::line('Imagick Available:', (string) $imagick);
		$data[] = Diagnostics::line('Imagick Enabled:', $settings['imagick'] ?? 'key not found in settings');
		$data[] = Diagnostics::line('Imagick Version:', (string) $imagick_version);
		$data[] = Diagnostics::line('GD Version:', $gd_version['GD Version']);

		return $next($data);
	}
}
