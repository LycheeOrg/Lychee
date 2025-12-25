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
use App\Repositories\ConfigManager;
use LycheeVerify\Verify;

/**
 * Check whether or not it is possible to update this installation.
 */
class SupporterCheck implements DiagnosticPipe
{
	/**
	 * @param Verify $verify
	 */
	public function __construct(
		private Verify $verify,
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if ($this->config_manager->getValueAsBool('disable_se_call_for_actions')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if (!$this->verify->is_supporter()) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::info('Have you considered supporting Lychee? :)', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}