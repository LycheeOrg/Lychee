<?php

/**
 * The MIT License (MIT).
 *
 * Copyright (c) laracraft-tech zacharias@laracraft.tech
 *
 * https://github.com/laracraft-tech/laravel-useful-additions#usefulenums3
 */

namespace App\Enum\Traits;

/**
 * add a 3 utility functions to enum.
 *
 * enum PaymentType: int
 * {
 *     use DecorateEnum;
 *
 *     case Pending = 1;
 *     case Failed = 2;
 *     case Success = 3;
 * }
 *
 * PaymentType::names();   // return ['Pending', 'Failed', 'Success']
 * PaymentType::values();  // return [1, 2, 3]
 * PaymentType::array();   // return ['Pending' => 1, 'Failed' => 2, 'Success' => 3]
 */
trait DecorateBackedEnum
{
	/**
	 * Returns a list of name covered by the enum.
	 *
	 * @return string[]
	 */
	public static function names(): array
	{
		return array_column(self::cases(), 'name');
	}

	/**
	 * Returns a list of values covered by the enum.
	 *
	 * @return (string|int)[]
	 */
	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	/**
	 * Returns an associative array [name => value].
	 *
	 * @return array<string,string|int>
	 */
	public static function array(): array
	{
		return array_combine(self::names(), self::values());
	}
}
