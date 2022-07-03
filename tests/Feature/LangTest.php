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
		$lang_available = Lang::get_lang_available();
		$keys = array_keys(Lang::get_lang());

		foreach ($lang_available as $code) {
			$lang_test = Lang::factory()->make($code);
			$locale = $lang_test->get_locale();

			foreach ($keys as $key) {
				static::assertArrayHasKey($key, $locale, 'Language ' . $lang_test->code() . ' is incomplete.');
			}
		}

		static::assertEquals('en', Lang::get_code());
		static::assertEquals('OK', Lang::get('SUCCESS'));

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
