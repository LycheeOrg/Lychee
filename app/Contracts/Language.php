<?php

namespace App\Contracts;

interface Language
{
	public static function code();

	public static function get_locale();
}
