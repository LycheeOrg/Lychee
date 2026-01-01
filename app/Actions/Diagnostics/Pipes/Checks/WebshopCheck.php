<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Actions\Shop\OrderService;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\OmnipayProviderType;
use App\Factories\OmnipayFactory;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;

/**
 * Check webshop configuration and environment conditions.
 */
class WebshopCheck implements DiagnosticPipe
{
	public function __construct(
		private OmnipayFactory $factory,
		private OrderService $order_service,
		private Verify $verify,
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

		if ($this->verify->get_status() !== Status::PRO_EDITION) {
			// Webshop not available in free edition
			return $next($data);
		}

		if (!$this->config_manager->getValueAsBool('webshop_enabled')) {
			return $next($data);
		}
		// @codeCoverageIgnoreStart
		if (config('omnipay.testMode', false) === true) {
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

		$number_broken_order = $this->order_service->selectClosedOrderNeedingFulfillmentQuery()->count();
		if ($number_broken_order > 0) {
			$data[] = DiagnosticData::error(
				'There are ' . $number_broken_order . ' closed orders with items that have no associated download link or size variant.',
				self::class,
				['Please check and assign the needed materials.']
			);
		}

		$number_waiting_order = $this->order_service->selectCompleteOrderNeedingFulfillmentQuery()->count();
		if ($number_waiting_order > 0) {
			$data[] = DiagnosticData::warn(
				'There are ' . $number_waiting_order . ' completed orders which require your attention.',
				self::class,
				['Please check and fulfill them in order to mark them as closed.']
			);
		}
		// @codeCoverageIgnoreEnd

		return $next($data);
	}
}