<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop\Gateway\Async;

use App\Contracts\Shop\AsyncPaymentGateway;
use App\Enum\OmnipayProviderType;

/**
 * Registry of the providers whose payments settle asynchronously.
 *
 * This is the single source of truth for "is this provider asynchronous?":
 * a provider is asynchronous exactly when a strategy is registered here.
 * Adding a new asynchronous provider means implementing
 * {@see AsyncPaymentGateway} and listing the implementation in the
 * constructor — nothing else needs to change.
 */
class AsyncPaymentRegistry
{
	/**
	 * @var array<string,AsyncPaymentGateway> keyed by provider value
	 */
	private array $gateways = [];

	/**
	 * @param PayzumAsyncGateway $payzum
	 */
	public function __construct(
		PayzumAsyncGateway $payzum,
	) {
		foreach ([$payzum] as $gateway) {
			$this->gateways[$gateway->provider()->value] = $gateway;
		}
	}

	/**
	 * The asynchronous strategy for a provider, or null for synchronous ones.
	 *
	 * @param OmnipayProviderType|null $provider
	 *
	 * @return AsyncPaymentGateway|null
	 */
	public function forProvider(?OmnipayProviderType $provider): ?AsyncPaymentGateway
	{
		if ($provider === null) {
			return null;
		}

		return $this->gateways[$provider->value] ?? null;
	}

	/**
	 * Whether payments of this provider settle asynchronously.
	 *
	 * @param OmnipayProviderType|null $provider
	 *
	 * @return bool
	 */
	public function isAsync(?OmnipayProviderType $provider): bool
	{
		return $this->forProvider($provider) !== null;
	}
}
