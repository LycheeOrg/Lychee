<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
	function page(Request $request, $page)
	{
		$page = Page::enabled()->where('link','/'.$page)->first();

		if($page == null)
			abort(404);

		$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());

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

		$menus = Page::menu()->get();

		$contents = $page->content;
//		dd($contents);

		return view('page', ['locale' => $lang, 'title' => $infos['title'], 'infos' => $infos, 'menus' => $menus, 'contents' => $contents]);
	}
}
