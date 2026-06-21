<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\Status;
use LycheeVerify\Rotation;
use LycheeVerify\Verify;

/**
 * Check if the current license is old or invalid.
 */
class OldLicenseCheck implements DiagnosticPipe
{
	public function __construct(
		private Verify $verify,
		private Rotation $rotation,
		protected readonly ConfigManager $config_manager,
	) {
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
		$current_license = $this->config_manager->getValueAsString('license_key');
		if ($current_license === '') {
			// No license set - skip check
			return $next($data);
		}

		if ($this->verify->get_status() !== Status::FREE_EDITION) {
			// Valid license - skip check
			return $next($data);
		}

		// @codeCoverageIgnoreStart
		/** @var string $api_key */
		$api_key = config('verify.keygen_api_key', '');
		if ($api_key !== '') {
			$result = $this->rotation->rotate();
			if ($result->success) {
				$this->verify->reset_status();

				return $next($data);
			}
		}
		// @codeCoverageIgnoreEnd

		$data[] = DiagnosticData::error('Your license has expired. Go to keygen.lycheeorg.dev to retrieve a new one or erase the value in the license field.', self::class);

		return $next($data);
	}
}
