<?php

namespace App\Locale;

interface LangInterface
{
	public static function code();

	public static function get_locale();
}
