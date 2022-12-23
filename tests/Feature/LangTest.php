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

use App\Facades\Lang;
use App\Factories\LangFactory;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
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
		static::assertEquals('en', Lang::get_code());
		static::assertEquals('OK', Lang::get('SUCCESS'));

		$msgSection = (new ConsoleOutput())->section();

		$englishDictionary = Lang::get_lang();
		$availableDictionaries = Lang::get_lang_available();
		$failed = false;

		foreach ($availableDictionaries as $locale) {
			$dictionary = Lang::factory()->make($locale)->get_locale();
			$missingKeys = array_diff_key($englishDictionary, $dictionary);
			foreach ($missingKeys as $key => $value) {
				$msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s misses the following key: %s', str_pad($locale, 8), $key));
				$failed = true;
			}

			$extraKeys = array_diff_key($dictionary, $englishDictionary);
			foreach ($extraKeys as $key => $value) {
				$msgSection->writeln(sprintf('<comment>Error:</comment> Locale %s has the following extra key: %s', str_pad($locale, 8), $key));
				$failed = true;
			}
		}
		static::assertFalse($failed);
	}

	public function testEnglishAsFallbackIfLangConfigIsMissing(): void
	{
		Configs::where('key', '=', 'lang')->delete();
		$lang = new \App\Locale\Lang(new LangFactory());
		$this->assertEquals('en', $lang->get_code());

		DB::table('configs')->insert([
			[
				'key' => 'lang',
				'value' => 'en',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => '',
			],
		]);
	}
}
