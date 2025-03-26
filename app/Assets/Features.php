<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Assets;

use App\Exceptions\Internal\FeaturesDoesNotExistsException;

/**
 * Manage enabled and disabled features.
 */
final class Features
{
	/**
	 * Determine whether a feature is active.
	 *
	 * @param string $feature_name to check
	 *
	 * @return bool is active
	 */
	public static function active(string $feature_name): bool
	{
		self::exists($feature_name);

		return config('features.' . $feature_name) === true;
	}

	/**
	 * Determine whether a feature is inactive.
	 *
	 * @param string $feature_name to check
	 *
	 * @return bool is inactive
	 */
	public static function inactive(string $feature_name): bool
	{
		self::exists($feature_name);

		return config('features.' . $feature_name) === false;
	}

	/**
	 * Determine if all of the given features are active.
	 *
	 * @param array<int,string> $feature_names to check
	 *
	 * @return bool is inactive
	 */
	public static function allAreActive(array $feature_names): bool
	{
		return array_reduce(
			$feature_names,
			fn ($bool, $feature_name) => $bool && self::active($feature_name),
			true);
	}

	/**
	 * Determine if any of the given features are active.
	 *
	 * @param array<int,string> $feature_names to check
	 *
	 * @return bool is inactive
	 */
	public static function someAreActive(array $feature_names): bool
	{
		return array_reduce(
			$feature_names,
			fn (bool $bool, string $feature_name) => $bool || self::active($feature_name),
			false);
	}

	/**
	 * Determine if all of the given features are inactive.
	 *
	 * @param array<int,string> $feature_names to check
	 *
	 * @return bool is inactive
	 */
	public static function allAreInactive(array $feature_names): bool
	{
		return array_reduce(
			$feature_names,
			fn (bool $bool, string $feature_name) => $bool && self::inactive($feature_name),
			true);
	}

	/**
	 * Determine if any of the given features are inactive.
	 *
	 * @param array<int,string> $feature_names to check
	 *
	 * @return bool is inactive
	 */
	public static function someAreInactive(array $feature_names): bool
	{
		return array_reduce(
			$feature_names,
			fn (bool $bool, string $feature_name) => $bool || self::inactive($feature_name),
			false);
	}

	/**
	 * Determine whether a feature is active.
	 *
	 * @template T
	 *
	 * @param string|array<int,string> $feature_names to check
	 * @param T|\Closure(): T          $val_if_true   what happens or Value if we features are enabled
	 * @param T|\Closure(): T          $val_if_false  what happens or Value if we features are disabled
	 *
	 * @return T
	 */
	public static function when(string|array $feature_names, mixed $val_if_true, mixed $val_if_false): mixed
	{
		$ret_value = match (is_array($feature_names)) {
			true => self::allAreActive($feature_names) ? $val_if_true : $val_if_false,
			false => self::active($feature_names) ? $val_if_true : $val_if_false,
		};

		return is_callable($ret_value) ? $ret_value() : $ret_value;
	}

	/**
	 * Assert whether the feature exists or not.
	 * Throws an exception if not.
	 *
	 * @param string $feature_name name of the feature to check
	 *
	 * @return void
	 *
	 * @throws FeaturesDoesNotExistsException
	 */
	private static function exists(string $feature_name): void
	{
		if (!is_bool(config('features.' . $feature_name))) {
			throw new FeaturesDoesNotExistsException(sprintf('No feature with name %s found.', $feature_name));
		}
	}
}