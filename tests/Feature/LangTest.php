<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use Symfony\Component\Console\Output\ConsoleOutput;
use Tests\AbstractTestCase;

class LangTest extends AbstractTestCase
{
	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testLanguageConsistency(): void
	{
		static::assertEquals('en', app()->getLocale());
		static::assertEquals('OK', __('lychee.SUCCESS'));

		$msgSection = (new ConsoleOutput())->section();

		$failed = false;

		$englishDictionaries = collect(array_diff(scandir(base_path('lang/en')), ['..', '.']))->filter(fn ($v) => str_ends_with($v, '.php'))->all();
		foreach ($englishDictionaries as $dictionaryFile) {
			$englishDictionary = include base_path('lang/en/' . $dictionaryFile);
			$availableDictionaries = array_diff(config('app.supported_locale'), ['en']);

			foreach ($availableDictionaries as $locale) {
				$dictionary = include base_path('lang/' . $locale . '/' . $dictionaryFile);
				$missingKeys = array_diff_key($englishDictionary, $dictionary);
				foreach ($missingKeys as $key => $value) {
					$msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s %s misses the following key: %s', str_pad($locale, 8), $dictionaryFile, $key));
					$failed = true;
				}

				$extraKeys = array_diff_key($dictionary, $englishDictionary);
				foreach ($extraKeys as $key => $value) {
					$msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s %s has the following extra key: %s', str_pad($locale, 8), $dictionaryFile, $key));
					$failed = true;
				}
				// $msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s %s is done', str_pad($locale, 8), $dictionaryFile));
			}
		}
		static::assertFalse($failed);
	}

	public function testEnglishAsFallbackIfLangConfigIsMissing(): void
	{
		app()->setLocale('ZK');
		static::assertEquals('ZK', app()->getLocale());
		static::assertEquals('OK', __('lychee.SUCCESS'));
	}
}
