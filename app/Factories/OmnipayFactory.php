<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Factories;

use App\Actions\Shop\Gateway\PaypalGateway;
use App\Enum\OmnipayProviderType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Shop\ProviderConfigurationNotFoundException;
use Omnipay\Common\GatewayInterface;
use Omnipay\Omnipay;

class OmnipayFactory
{
	/**
	 * Create a payment gateway instance.
	 *
	 * @param OmnipayProviderType $provider
	 *
	 * @return GatewayInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function create_gateway(OmnipayProviderType $provider): GatewayInterface
	{
		$gateway = match ($provider) {
			OmnipayProviderType::PAYPAL => new PaypalGateway(), // home backed...
			OmnipayProviderType::MOLLIE,
			OmnipayProviderType::DUMMY => Omnipay::create($provider->value),
		};

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
		if (!is_array($param) || count($param) === 0) {
			throw new LycheeLogicException('No configuration found for provider ' . $provider->value);
		}

		if (!$this->isValidateProvider($provider, $param)) {
			throw new ProviderConfigurationNotFoundException($provider);
		}

		$gateway->initialize($param);

		return $gateway;
	}

	/**
	 * Get the list of supported payment providers based on configuration.
	 *
	 * @return OmnipayProviderType[]
	 */
	public function get_supported_providers(): array
	{
		$all_providers = OmnipayProviderType::cases();
		$supported_providers = [];

		foreach ($all_providers as $provider) {
			if (!$provider->isAllowed()) {
				continue;
			}

			$param = config('omnipay.' . $provider->value);
			if (is_array($param) &&
				count($param) > 0 &&
				$this->isValidateProvider($provider, $param)
			) {
				$supported_providers[] = $provider;
			}
		}

		return $supported_providers;
	}

	/**
	 * At least one of the required parameters is set.
	 *
	 * @param OmnipayProviderType $provider
	 * @param string[]            $params
	 *
	 * @return bool
	 */
	private function isValidateProvider(OmnipayProviderType $provider, array $params): bool
	{
		foreach ($provider->requiredKeys() as $key) {
			if (!array_key_exists($key, $params) || $params[$key] === '') {
				return false;
			}
		}

		return true;
	}
}