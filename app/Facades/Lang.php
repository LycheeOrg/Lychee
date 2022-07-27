<?php

namespace App\Facades;

use App\Factories\LangFactory;
use Illuminate\Support\Facades\Facade;

/**
 * Facade Lang.
 *
 * Provides static access to methods of {@link \App\Locale\Lang}.
 *
 * Keep the list of documented method in sync with {@link \App\Locale\Lang}.
 *
 * @method static string get(string $string)
 * @method static string get_code()
 * @method static string[] get_lang()
 * @method static string[] get_lang_available()
 * @method static LangFactory factory()
 */
class Lang extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'lang';
	}
}
