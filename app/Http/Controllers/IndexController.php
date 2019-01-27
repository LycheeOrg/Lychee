<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;

class IndexController extends Controller
{

	public function show()
	{

		$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());

		return view('landing', ['locale' => $lang, 'title' => config('app.name')]);
//		return $this->gallery();
	}


	public function phpinfo()
	{
		return (string) phpinfo();
	}

	public function gallery()
	{
		$lang = Lang::get_lang(Configs::get_value('lang'));

		return view('gallery', ['locale' => $lang, 'title' => config('app.name')]);
	}
}
