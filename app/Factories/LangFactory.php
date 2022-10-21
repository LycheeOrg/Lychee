<?php

namespace App\Factories;

use App\Contracts\Language;
use App\Locale\English;
use function Safe\scandir;

class LangFactory
{
	/**
	 * Maps a language code to the name of class which implements this
	 * language.
	 *
	 * @var array<string, class-string<\App\Contracts\Language>>
	 */
	private array $langs = [];

	public function __construct()
	{
		$lang_namespace = 'App\Locale';
		$list_lang = scandir(__DIR__ . '/../Locale');

		for ($i = 0; $i < count($list_lang); $i++) {
			$class_candidate = $lang_namespace . '\\' . substr($list_lang[$i], 0, -4);
			if (is_subclass_of($class_candidate, Language::class)) {
				/** @var Language */
				$lang = new $class_candidate();
				$this->langs[$lang->code()] = $class_candidate;
			}
		}
	}

	public function exists(string $code): bool
	{
		return array_key_exists($code, $this->langs);
	}

	/**
	 * Factory method.
	 */
	public function make(string $kind): Language
	{
		if ($this->exists($kind)) {
			return resolve($this->langs[$kind]);
		}

		return resolve(English::class);
	}

	/**
	 * @return string[]
	 */
	public function getCodes(): array
	{
		return array_keys($this->langs);
	}
}
