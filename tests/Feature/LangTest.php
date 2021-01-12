<?php

namespace Tests\Feature;

use Lang;
use Tests\TestCase;

class LangTest extends TestCase
{
	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testLang()
	{
		$lang_available = Lang::get_lang_available();
		$keys = array_keys(Lang::get_lang());

		foreach ($lang_available as $code) {
			$lang_test = Lang::factory()->make($code);
			$locale = $lang_test->get_locale();

			foreach ($keys as $key) {
				$this->assertArrayHasKey($key, $locale, 'Language ' . $lang_test->code() . ' is incomplete.');
			}
		}
	}
}
