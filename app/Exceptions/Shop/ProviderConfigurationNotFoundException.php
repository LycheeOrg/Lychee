<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Shop;

use App\Enum\OmnipayProviderType;
use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class ProviderConfigurationNotFoundException extends BaseLycheeException
{
	public function __construct(OmnipayProviderType $provider)
	{
		parent::__construct(
			Response::HTTP_NOT_IMPLEMENTED,
			sprintf('%s does not have the configuration key [%s] required', $provider->value, implode(', ', $provider->requiredKeys())),
		);
	}
}