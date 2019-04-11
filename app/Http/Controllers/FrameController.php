<?php

namespace App\Http\Controllers;


use App\Configs;
use App\Locale\Lang;
use App\Response;


class FrameController extends Controller
{
	/**
	 * @return false|string
	 */
	function init()
	{
		Configs::get();

		if (Configs::get_value('Mod_Frame') != '1') {
			return redirect()->route('home');
		}

		$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());
		$lang['language'] = Configs::get_value('lang');

		$infos = array();
		$infos['owner'] = Configs::get_value('landing_owner');
		$infos['title'] = Configs::get_value('landing_title');
		$infos['subtitle'] = Configs::get_value('landing_subtitle');
		$infos['facebook'] = Configs::get_value('landing_facebook');
		$infos['flickr'] = Configs::get_value('landing_flickr');
		$infos['twitter'] = Configs::get_value('landing_twitter');
		$infos['instagram'] = Configs::get_value('landing_instagram');
		$infos['youtube'] = Configs::get_value('landing_youtube');
		$infos['background'] = Configs::get_value('landing_background');

		$title = Configs::get_value('site_title');

		return view('frame', ['locale' => $lang, 'title' => $title, 'infos' => $infos]);

	}

	function getSettings()
	{
		Configs::get();

		if(Configs::get_value('Mod_Frame') != '1') {
			return Response::error('Frame is not enabled');
		}

		$return = array();
		$return['refresh'] = Configs::get_value('Mod_Frame_refresh');

		return $return;

	}

}