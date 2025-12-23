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
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminUserExistsCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		if (!Schema::hasTable('users')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		$number_of_admin = User::query()->where('may_administrate', '=', true)->count();
		if ($number_of_admin === 0) {
			// @codeCoverageIgnoreStart
			$data->data[] = DiagnosticData::error('User Admin not found in database. Please run: "php lychee:create_user {username} {password}"', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
