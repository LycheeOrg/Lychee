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
	 * @param string $featureName to check
	 *
	 * @return bool is active
	 */
	public static function active(string $featureName): bool
	{
		self::exists($featureName);

		return config('features.' . $featureName) === true;
	}

	/**
	 * Determine whether a feature is inactive.
	 *
	 * @param string $featureName to check
	 *
	 * @return bool is inactive
	 */
	public static function inactive(string $featureName): bool
	{
		self::exists($featureName);

		return config('features.' . $featureName) === false;
	}

	/**
	 * Determine if all of the given features are active.
	 *
	 * @param array<int,string> $featureNames to check
	 *
	 * @return bool is inactive
	 */
	public static function allAreActive(array $featureNames): bool
	{
		return array_reduce(
			$featureNames,
			fn ($bool, $featureName) => $bool && self::active($featureName),
			true);
	}

	/**
	 * Determine if any of the given features are active.
	 *
	 * @param array<int,string> $featureNames to check
	 *
	 * @return bool is inactive
	 */
	public static function someAreActive(array $featureNames): bool
	{
		return array_reduce(
			$featureNames,
			fn (bool $bool, string $featureName) => $bool || self::active($featureName),
			false);
	}

	/**
	 * Determine if all of the given features are inactive.
	 *
	 * @param array<int,string> $featureNames to check
	 *
	 * @return bool is inactive
	 */
	public static function allAreInactive(array $featureNames): bool
	{
		return array_reduce(
			$featureNames,
			fn (bool $bool, string $featureName) => $bool && self::inactive($featureName),
			true);
	}

	/**
	 * Determine if any of the given features are inactive.
	 *
	 * @param array<int,string> $featureNames to check
	 *
	 * @return bool is inactive
	 */
	public static function someAreInactive(array $featureNames): bool
	{
		return array_reduce(
			$featureNames,
			fn (bool $bool, string $featureName) => $bool || self::inactive($featureName),
			false);
	}

	/**
	 * Determine whether a feature is active.
	 *
	 * @template T
	 *
	 * @param string|array<int,string> $featureNames to check
	 * @param T|\Closure(): T          $valIfTrue    what happens or Value if we features are enabled
	 * @param T|\Closure(): T          $valIfFalse   what happens or Value if we features are disabled
	 *
	 * @return T
	 */
	public static function when(string|array $featureNames, mixed $valIfTrue, mixed $valIfFalse): mixed
	{
		$retValue = match (is_array($featureNames)) {
			true => self::allAreActive($featureNames) ? $valIfTrue : $valIfFalse,
			false => self::active($featureNames) ? $valIfTrue : $valIfFalse,
		};

		return is_callable($retValue) ? $retValue() : $retValue;
	}

	/**
	 * Assert whether the feature exists or not.
	 * Throws an exception if not.
	 *
	 * @param string $featureName name of the feature to check
	 *
	 * @return void
	 *
	 * @throws FeaturesDoesNotExistsException
	 */
	private static function exists(string $featureName): void
	{
		if (!is_bool(config('features.' . $featureName))) {
			throw new FeaturesDoesNotExistsException(sprintf('No feature with name %s found.', $featureName));
		}
	}
}
