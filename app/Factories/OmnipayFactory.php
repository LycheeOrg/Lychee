<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Factories;

use App\Enum\OmnipayProviderType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Shop\ProviderConfigurationNotFoundException;
use Omnipay\Common\GatewayInterface;
use Omnipay\Omnipay;

class OmnipayFactory
{
	/**
	 * Create a payment gateway instannce.
	 *
	 * @param OmnipayProviderType $provider
	 *
	 * @return GatewayInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function create_gateway(OmnipayProviderType $provider): GatewayInterface
	{
		$gateway = Omnipay::create($provider->value);

		$gateway = $this->initialize_gateway($gateway, $provider);

		return $gateway;
	}

	/**
	 * @param GatewayInterface    $gateway
	 * @param OmnipayProviderType $provider
	 *
	 * @return GatewayInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	private function initialize_gateway(GatewayInterface $gateway, OmnipayProviderType $provider): GatewayInterface
	{
		$param = config('omnipay.' . $provider->value);
		if ($param === null) {
			throw new LycheeLogicException('No configuration found for provider ' . $provider->value);
		}

		foreach ($param as $key => $value) {
			if ($value === null || $value === '') {
				throw new ProviderConfigurationNotFoundException($key, $provider);
			}
		}

		$gateway->initialize($param);

		return $gateway;
	}
}