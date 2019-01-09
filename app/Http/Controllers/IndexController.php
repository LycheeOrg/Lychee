<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\Logs;

class IndexController extends Controller
{

	public function show()
	{
		$lang = Lang::get_lang(Configs::get_value('lang'));

		return view('index', ['locale' => $lang]);
	}

}