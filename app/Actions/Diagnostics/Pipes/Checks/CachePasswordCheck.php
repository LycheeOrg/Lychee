<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * We check wether the config is set to use Cache and if there are password sets for albums.
 */
class CachePasswordCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if (Configs::getValueAsBool('cache_enabled') && DB::table(APC::ACCESS_PERMISSIONS)->whereNotNull('password')->count() > 0) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('Response cache is enabled and some albums are password protected.', self::class, ['Due to response caching, unlocking those albums will reveal their content to other annonymous users.']);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
