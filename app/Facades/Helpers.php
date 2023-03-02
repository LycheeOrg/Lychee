<?php

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
 * @method static string trancateIf32(string $id, int $prevShortId = 0, int $phpMax)
 * @method static string getExtension(string $filename, bool $isURI = false)
 * @method static bool   hasPermissions(string $path)
 * @method static bool   hasFullPermissions(string $path)
 * @method static int    gcd(int $a, int $b)
 * @method static int    data_index()
 * @method static int    data_index_r()
 * @method static void   data_index_set(int $idx = 0)
 * @method static array  get_all_licenses()
 */
class Helpers extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'Helpers';
	}
}
