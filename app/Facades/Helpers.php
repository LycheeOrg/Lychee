<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Helpers.
 *
 * Provides static access to methods of {@link \App\Assets\Helpers}.
 *
 * Keep the list of documented method in sync with {@link \App\Assets\Helpers}.
 *
 * @method static string cacheBusting(string $filePath)
 * @method static string trancateIf32(string $id, int $prevShortId = 0, int $phpMax = PHP_INT_MAX)
 * @method static string getExtension(string $filename, bool $isURI = false)
 * @method static bool   hasPermissions(string $path)
 * @method static bool   hasFullPermissions(string $path)
 * @method static int    gcd(int $a, int $b)
 * @method static bool   isExecAvailable()
 * @method static string secondsToHMS(int|float $d)
 * @method static int    convertSize(string $size)
 * @method static string decimalToDegreeMinutesSeconds(float $decimal, bool $type)
 * @method static string censor(string $string, float  $percentOfClear = 0.5)
 * @method static string getUriWithQueryString(\Illuminate\Http\Request $request): string
 */
class Helpers extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'Helpers';
	}
}
