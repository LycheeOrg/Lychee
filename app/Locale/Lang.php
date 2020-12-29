<?php

namespace App\Locale;

use App\Contracts\Language;
use App\Factories\LangFactory;
use App\Models\Configs;

class Lang
{
	/** @var LangFactory */
	private $langFactory;

	/** @var string */
	private $code;

	/** @var Language */
	private $language;

	/**
	 * Initialize the Facade.
	 */
	public function __construct(LangFactory $langFactory)
	{
		$this->langFactory = $langFactory;

		$this->code = Configs::get_value('lang', 'en');

		$this->language = $langFactory->make($this->code);
	}

	/**
	 * Quickly translate a string (used with the Facade).
	 */
	public function get(string $string)
	{
		return $this->language->get_locale()[$string];
	}

	/**
	 * Return code (mostly for HTML).
	 */
	public function get_code()
	{
		return $this->language->code();
	}

	/**
	 * Return the language array (AJAX initialization).
	 */
	public function get_lang()
	{
		return $this->language->get_locale();
	}

	/**
	 * Return the languages available (AJAX initialization & settings).
	 */
	public function get_lang_available()
	{
		return $this->langFactory->getCodes();
	}
}
