<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticDTO;
use App\Facades\Helpers;

/**
 * Info on what image processing we have available.
 */
class ExtensionsInfo implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
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
		$data[] = Diagnostics::line('Imagick Enabled:', $data->config_manager->getValueAsString('imagick'));
		$data[] = Diagnostics::line('Imagick Version:', (string) $imagick_version);
		$data[] = Diagnostics::line('GD Version:', $gd_version['GD Version']);

		return $next($data);
	}
}
