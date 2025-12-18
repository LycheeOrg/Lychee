<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;

/**
 * Check if the current license is old or invalid.
 */
class OldLicenseCheck implements DiagnosticPipe
{
	public function __construct(private Verify $verify)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		// Load settings
		$current_license = Configs::getValueAsString('license_key');
		if ($current_license === '') {
			// No license set - skip check
			return $next($data);
		}

		if ($this->verify->get_status() !== Status::FREE_EDITION) {
			// Valid license - skip check
			return $next($data);
		}

		$data[] = DiagnosticData::error('Your license has expired. Go to keygen.lycheeorg.dev to retrieve a new one or erase the value in the license field.', self::class);

		return $next($data);
	}
}
