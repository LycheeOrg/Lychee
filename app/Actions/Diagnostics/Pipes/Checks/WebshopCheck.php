<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\OmnipayProviderType;
use App\Factories\OmnipayFactory;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;

/**
 * Check webshop configuration and environment conditions.
 */
class WebshopCheck implements DiagnosticPipe
{
	public function __construct(
		private OmnipayFactory $factory,
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

		if (!Configs::getValueAsBool('webshop_enabled')) {
			return $next($data);
		}
		// @codeCoverageIgnoreStart
		if (config('omnipay.test_mode', false) === true) {
			$data[] = DiagnosticData::warn(
				'Webshop is running in test mode.',
				self::class,
				['This means that payments won\'t be executed.', 'Users may use it to get free content.']
			);
		}

		if (config('app.env', 'production') !== 'production') {
			$data[] = DiagnosticData::warn(
				'Webshop is enabled but the application is not running in production mode.',
				self::class,
				['This means that the dummy payment gateway is available.', 'Users may use it to get free content.']
			);
		}

		$supported_providers = $this->factory->get_supported_providers();
		if (count($supported_providers) === 0) {
			$data[] = DiagnosticData::error(
				'Webshop is enabled but no payment provider is configured.',
				self::class,
				['No payment can be processed.']
			);
		} else {
			$provider_names = array_map(static fn (OmnipayProviderType $provider): string => $provider->value, $supported_providers);
			$data[] = DiagnosticData::info(
				'Webshop is enabled with the following payment providers: ' . implode(', ', $provider_names),
				self::class
			);
		}
		// @codeCoverageIgnoreEnd

		return $next($data);
	}
}