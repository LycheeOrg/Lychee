<?php

namespace App\Locale;

use App\Contracts\Language;
use App\Models\Configs;

class Lang
{
	private $code;

	/**
	 * @var Language
	 */
	private $language;

	public function __construct()
	{
		$this->code = Configs::get_value('lang', 'en');

		$list_lang = $this->get_classes();

		$found = false;
		for ($i = 0; $i < count($list_lang); $i++) {
			$language = new $list_lang[$i]();
			if ($language->code() == $this->code) {
				$this->language = $language;
				$found = true;
				break;
			}
		}

		// default: we force English
		if (!$found) {
			$this->language = new English();
		}
	}

	private function get_classes()
	{
		$return = [];
		$list_lang = scandir(__DIR__);
		$contract = 'App\Contracts\Language';

		for ($i = 0; $i < count($list_lang); $i++) {
			$class_candidate = __NAMESPACE__ . '\\' . substr($list_lang[$i], 0, -4);
			if (
				is_subclass_of($class_candidate, $contract)
			) {
				$return[] = __NAMESPACE__ . '\\' . substr($list_lang[$i], 0, -4);
			}
		}

		return $return;
	}

	public function get_lang()
	{
		return $this->language->get_locale();
	}

	public function get_lang_available()
	{
		$list_lang = $this->get_classes();
		$return = [];
		for ($i = 0; $i < count($list_lang); $i++) {
			$language = new $list_lang[$i]();
			$return[] = $language->code();
		}

		return $return;
	}
}
