<?php

namespace Tests\Feature;

use App\Locale\Lang;
use App\Logs;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LangTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testLang()
    {
	    $lang_availables = Lang::get_lang_available();

//	    $this->markTestSkipped('Some language are still missing some keys...');

		$keys = array_keys(Lang::get_lang());
		foreach ($lang_availables as $lang)
		{
			$lang_test = Lang::get_lang($lang);

			foreach ($keys as $key){
				$this->assertArrayHasKey($key,$lang_test, 'Language '.$lang.' is incomplete.');
			}
		}
    }
}
