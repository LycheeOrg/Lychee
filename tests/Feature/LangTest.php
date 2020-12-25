<?php

namespace Tests\Feature;

use App\Contract\Language;
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
		$lang_available = Lang::get_classes();
		$keys = array_keys(Lang::get_lang());

		$lang_available->each(function ($item, $key) use ($keys) {
			/**
			 * @var Language
			 */
			$lang_test = new $item();
			$locale = $lang_test->get_locale();

			foreach ($keys as $key) {
				$this->assertArrayHasKey($key, $locale, 'Language ' . $lang_test->code() . ' is incomplete.');
			}
		});
	}
}
