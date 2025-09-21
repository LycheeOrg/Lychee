<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Casts;

use App\Services\MoneyService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

/**
 * Cast an integer cents value from the database to a Money object and vice versa.
 */
class MoneyCast implements CastsAttributes
{
	/**
	 * The currency to use for the Money object.
	 */
	protected ?string $currency_code;

	/**
	 * Money service instance.
	 */
	protected MoneyService $money_service;

	/**
	 * Create a new cast class instance.
	 *
	 * @param string|null $currency_code The currency code to use (defaults to config value)
	 */
	public function __construct(?string $currency_code = null)
	{
		$this->money_service = resolve(MoneyService::class);
		$this->currency_code = $currency_code ?? $this->money_service->getDefaultCurrencyCode();
	}

	/**
	 * Cast the given value from database integer (cents) to a Money object.
	 *
	 * @param Model  $model      The model being cast
	 * @param string $key        The attribute name
	 * @param mixed  $value      The database value (integer cents)
	 * @param array  $attributes All model attributes
	 *
	 * @return Money The Money object or null if value is null
	 */
	public function get($model, string $key, $value, array $attributes): Money
	{
		$currency_code = $this->currency_code ?? $this->money_service->getDefaultCurrencyCode();

		if ($value === null) {
			return new Money(0, new Currency($currency_code));
		}

		return new Money($value, new Currency($currency_code));
	}

	/**
	 * Cast the given Money object to an integer cents value for database storage.
	 *
	 * @param Model  $model      The model being cast
	 * @param string $key        The attribute name
	 * @param mixed  $value      The Money object
	 * @param array  $attributes All model attributes
	 *
	 * @return array<string,int|null> The integer value in cents for database storage
	 *
	 * @throws \InvalidArgumentException If value is not a Money object
	 */
	public function set($model, string $key, $value, array $attributes): array
	{
		if ($value === null) {
			return [$key => null];
		}

		if (!$value instanceof Money) {
			throw new \InvalidArgumentException('The given value is not a Money instance.');
		}

		return [$key => (int) $value->getAmount()];
	}
}