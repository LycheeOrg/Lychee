<?php

namespace App\Locale;

use App\Contracts\Language;
use App\Models\Configs;
use Illuminate\Support\Collection as BaseCollection;

class Lang
{
	private $code;

	/**
	 * @var Language
	 */
	private $language;

	/**
	 * Initialize the Facade.
	 */
	public function __construct()
	{
		$this->code = Configs::get_value('lang', 'en');

		$list_lang = $this->get_classes()->map(fn ($l) => new $l())
			->filter(fn ($l) => $l->code() == $this->code);

		// default: we force English
		if ($list_lang->isEmpty()) {
			$this->language = new English();
		} else {
			$this->language = $list_lang->first();
		}
	}

	public function get_classes(): BaseCollection
	{
		$return = new BaseCollection();

		$contract = 'App\Contracts\Language';
		$list_lang = scandir(__DIR__);

		for ($i = 0; $i < count($list_lang); $i++) {
			$class_candidate = __NAMESPACE__ . '\\' . substr($list_lang[$i], 0, -4);
			if (
				is_subclass_of($class_candidate, $contract)
			) {
				$return->push(__NAMESPACE__ . '\\' . substr($list_lang[$i], 0, -4));
			}
		}

		return $return;
	}

	public function get(string $string)
	{
		return $this->language->get_locale()[$string];
	}

	public function get_code()
	{
		return $this->language->code();
	}

	public function get_lang()
	{
		return $this->language->get_locale();
	}

	public function get_lang_available()
	{
		return $this->get_classes()->map(fn ($l) => (new $l())->code());
	}
}
