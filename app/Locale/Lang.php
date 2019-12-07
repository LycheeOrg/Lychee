<?php

namespace App\Locale;

class Lang
{
	public static function get_classes()
	{
		$return = [];
		$list_lang = scandir(__DIR__);
		for ($i = 0; $i < count($list_lang); $i++) {
			if ($list_lang[$i] != '.' &&
				$list_lang[$i] != '..' &&
				$list_lang[$i] != 'Lang.php' &&
				$list_lang[$i] != 'LangInterface.php'
			) {
				$return[] = __NAMESPACE__ . '\\' . substr($list_lang[$i], 0, -4);
			}
		}

		return $return;
	}

	public static function get_lang($value = 'en')
	{
		$list_lang = Lang::get_classes();
		for ($i = 0; $i < count($list_lang); $i++) {
			if ($list_lang[$i]::code() == $value) {
				return $list_lang[$i]::get_locale();
			}
		}

		// default: we force English
		/** @var LangInterface $class_name */
		$class_name = __NAMESPACE__ . '\\' . 'English';

		return $class_name::get_locale();
	}

	public static function get_lang_available()
	{
		$list_lang = Lang::get_classes();
		$return = [];
		for ($i = 0; $i < count($list_lang); $i++) {
			$return[] = $list_lang[$i]::code();
		}

		return $return;
	}
}
