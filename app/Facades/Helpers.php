<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Helpers.
 *
 * @method static string generateID()
 */
class Helpers extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'Helpers';
	}
}
