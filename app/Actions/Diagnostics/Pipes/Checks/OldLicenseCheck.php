<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\DTO\DiagnosticDTO;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\Status;

/**
 * Check if the current license is old or invalid.
 */
class OldLicenseCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		// Load settings
		$current_license = $data->config_manager->getValueAsString('license_key');
		if ($current_license === '') {
			// No license set - skip check
			return $next($data);
		}

		if ($data->verify->get_status() !== Status::FREE_EDITION) {
			// Valid license - skip check
			return $next($data);
		}

		$data[] = DiagnosticData::error('Your license has expired. Go to keygen.lycheeorg.dev to retrieve a new one or erase the value in the license field.', self::class);

		return $next($data);
	}
}
