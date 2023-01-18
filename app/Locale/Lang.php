<?php

namespace App\Locale;

use App\Contracts\Language;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\MissingTranslationException;
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

		// Necessary for phpStan to pass.
		try {
			$this->code = Configs::getValueAsString('lang');
		} catch (ConfigurationKeyMissingException) {
			$this->code = 'en';
		}

		$this->language = $langFactory->make($this->code);
	}

	/**
	 * Quickly translate a string (used with the Facade).
	 */
	public function get(string $string, ?string $default = null): string
	{
		return $this->language->get_locale()[$string] ?? $default ?? throw new MissingTranslationException($string);
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
