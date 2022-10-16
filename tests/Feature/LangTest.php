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
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tests\TestCase;

class LangTest extends TestCase
{
	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testLang(): void
	{
		$msgSection = (new ConsoleOutput())->section();

		$failed = false;

		$lang_available = Lang::get_lang_available();
		$keys = array_keys(Lang::get_lang());

		foreach ($lang_available as $code) {
			$lang_test = Lang::factory()->make($code);
			$locale = $lang_test->get_locale();

			foreach ($keys as $key) {
				try {
					static::assertArrayHasKey($key, $locale, 'Language ' . $lang_test->code() . ' is incomplete.');
				} catch (ExpectationFailedException $e) {
					$msgSection->writeln('<comment>Error:</comment> ' . $e->getMessage());
					$failed = true;
				}
			}
		}

		static::assertEquals('en', Lang::get_code());
		static::assertEquals('OK', Lang::get('SUCCESS'));
		$this->assertFalse($failed);
	}

	/**
	 * Test Languages are strictly.
	 *
	 * @return void
	 */
	public function testLangOverflow(): void
	{
		$msgSection = (new ConsoleOutput())->section();

		$failed = false;

		$full = Lang::get_lang();
		$lang_available = Lang::get_lang_available();

		foreach ($lang_available as $code) {
			$lang_test = Lang::factory()->make($code);
			$keys = array_keys($lang_test->get_locale());

			foreach ($keys as $key) {
				try {
					static::assertArrayHasKey($key, $full, 'Language ' . $lang_test->code() . ' as too many keys.');
				} catch (ExpectationFailedException $e) {
					$msgSection->writeln('<comment>Error:</comment> ' . $e->getMessage());
					$failed = true;
				}
			}
		}
		$this->assertFalse($failed);
	}

	public function testEnglishAsFallbackIfLangConfigIsMissing(): void
	{
		Configs::where('key', '=', 'lang')->delete();
		$lang = new \App\Locale\Lang(new LangFactory());
		self::assertEquals('en', $lang->get_code());

		DB::table('configs')->insert([
			[
				'key' => 'lang',
				'value' => 'en',
				'confidentiality' => 0,
				'cat' => 'Gallery',
			],
		]);
	}
}
