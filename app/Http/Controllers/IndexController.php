<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;

class IndexController extends Controller
{

	public function show()
	{

		$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());

		return view('index', ['locale' => $lang]);
	}

}