<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Services;

use App\Models\Configs;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

/**
 * Service for handling money and currency operations.
 */
class MoneyService
{
	/**
	 * Get the default currency code from config.
	 *
	 * @return string The default currency code
	 */
	public function getDefaultCurrencyCode(): string
	{
		return Configs::getValueAsString('webshop_currency');
	}

	/**
	 * Create a Money object from cents.
	 *
	 * @param int         $cents         Amount in cents
	 * @param string|null $currency_code Optional currency code
	 *
	 * @return Money
	 */
	public function createFromCents(int $cents, ?string $currency_code = null): Money
	{
		$currency_code = $currency_code ?? $this->getDefaultCurrencyCode();

		return new Money($cents, new Currency($currency_code));
	}

	/**
	 * Create a Money object from dollars.
	 *
	 * @param string      $amount        Amount in decimal format
	 * @param string|null $currency_code Optional currency code
	 *
	 * @return Money
	 */
	public function createFromDecimal(string $amount, ?string $currency_code = null): Money
	{
		$currency_code = $currency_code ?? $this->getDefaultCurrencyCode();
		$currency = new Currency($currency_code);
		$currencies = new ISOCurrencies();

		$money_parser = new DecimalMoneyParser($currencies);

		return $money_parser->parse($amount, $currency);
	}

	/**
	 * Format a Money object to a human-readable string.
	 *
	 * @param Money $money The Money object to format
	 *
	 * @return string Formatted money string with currency symbol
	 */
	public function format(Money $money): string
	{
		$number_formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);
		$currencies = new ISOCurrencies();
		$money_formatter = new IntlMoneyFormatter($number_formatter, $currencies);

		return $money_formatter->format($money);
	}

	/**
	 * Convert a Money object to a decimal value.
	 *
	 * @param Money $money The Money object to convert
	 *
	 * @return string Decimal representation of the money amount
	 */
	public function toDecimal(Money $money): string
	{
		$currencies = new ISOCurrencies();
		$money_formatter = new DecimalMoneyFormatter($currencies);

		return $money_formatter->format($money);
	}
}
