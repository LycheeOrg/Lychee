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

/**
 * Check if the watermarker is properly configured and enabled.
 */
class WebshopCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		if (!Configs::getValueAsBool('webshop_enabled')) {
			return $next($data);
		}

		if (config('app.env', 'production') !== 'production') {
			$data[] = DiagnosticData::warn(
				'Webshop is enabled but the application is not running in production mode.',
				self::class,
				['This means that the dummy payment gateway is available.','Users may use it to get free content.']
			);
		}

		return $next($data);
	}
}