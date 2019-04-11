<?php

namespace Tests\Feature;

use App\Locale\Lang;
use Tests\TestCase;

class LangTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testLang()
    {
	    $lang_available = Lang::get_lang_available();

		$keys = array_keys(Lang::get_lang());
		foreach ($lang_available as $lang)
		{
			$lang_test = Lang::get_lang($lang);

			foreach ($keys as $key){
				$this->assertArrayHasKey($key,$lang_test, 'Language '.$lang.' is incomplete.');
			}
		}
    }
}
