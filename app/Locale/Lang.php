<?php

namespace App\Locale;

use App\Contracts\Language;
use App\Factories\LangFactory;
use App\Models\Configs;

class Lang
{
	private LangFactory $langFactory;
	private string $code;
	private Language $language;

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
	public function get(string $string): string
	{
		return $this->language->get_locale()[$string];
	}

	/**
	 * Return code (mostly for HTML).
	 */
	public function get_code(): string
	{
		return $this->language->code();
	}

	/**
	 * Return the language array (AJAX initialization).
	 *
	 * @return string[]
	 */
	public function get_lang(): array
	{
		return $this->language->get_locale();
	}

	/**
	 * Return the languages available (AJAX initialization & settings).
	 *
	 * @return string[]
	 */
	public function get_lang_available(): array
	{
		return $this->langFactory->getCodes();
	}

	public function factory(): LangFactory
	{
		return $this->langFactory;
	}
}
