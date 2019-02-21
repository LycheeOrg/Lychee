<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;

class IndexController extends Controller
{

	public function show()
	{

		if (Configs::get_value('landing_page_enable', '0') == '1')
		{
			$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());

			$infos['owner'] = Configs::get_value('landing_owner');
			$infos['title'] = Configs::get_value('landing_title');
			$infos['subtitle'] = Configs::get_value('landing_subtitle');
			$infos['facebook'] = Configs::get_value('landing_facebook');
			$infos['flickr'] = Configs::get_value('landing_flickr');
			$infos['twitter'] = Configs::get_value('landing_twitter');
			$infos['instagram'] = Configs::get_value('landing_instagram');
			$infos['youtube'] = Configs::get_value('landing_youtube');
			$infos['background'] = Configs::get_value('landing_background');


			return view('landing', ['locale' => $lang, 'title' => $infos['title'], 'infos' => $infos]);
		}

		return $this->gallery();
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
