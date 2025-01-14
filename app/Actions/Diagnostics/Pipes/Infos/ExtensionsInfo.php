<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
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
			$imagickVersion = \Imagick::getVersion();
		} else {
			// @codeCoverageIgnoreStart
			$imagick = '-';
			// @codeCoverageIgnoreEnd
		}
		if (!isset($imagickVersion, $imagickVersion['versionNumber'])) {
			// @codeCoverageIgnoreStart
			$imagickVersion = '-';
		// @codeCoverageIgnoreEnd
		} else {
			$imagickVersion = $imagickVersion['versionNumber'];
		}

		// About GD version
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
		} else {
			// @codeCoverageIgnoreStart
			$gdVersion = ['GD Version' => '-'];
			// @codeCoverageIgnoreEnd
		}

		$data[] = Diagnostics::line('exec() Available:', Helpers::isExecAvailable() ? 'yes' : 'no');
		$data[] = Diagnostics::line('Imagick Available:', (string) $imagick);
		$data[] = Diagnostics::line('Imagick Enabled:', $settings['imagick'] ?? 'key not found in settings');
		$data[] = Diagnostics::line('Imagick Version:', (string) $imagickVersion);
		$data[] = Diagnostics::line('GD Version:', $gdVersion['GD Version']);

		return $next($data);
	}
}
