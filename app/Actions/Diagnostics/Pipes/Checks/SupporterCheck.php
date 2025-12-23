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

/**
 * Check whether or not it is possible to update this installation.
 */
class SupporterCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		if ($data->config_manager->getValueAsBool('disable_se_call_for_actions')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if (!$data->verify->is_supporter()) {
			// @codeCoverageIgnoreStart
			$data->data[] = DiagnosticData::info('Have you considered supporting Lychee? :)', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}