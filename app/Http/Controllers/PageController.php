<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PageController extends Controller
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;



	/**
	 * @param ConfigFunctions $configFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions)
	{
		$this->configFunctions = $configFunctions;
	}


	function page(Request $request, $page)
	{
		$page = Page::enabled()->where('link','/'.$page)->first();

		if($page == null)
			abort(404);

		$lang = Lang::get_lang(Configs::get_value('lang'));
		$lang['language'] = Configs::get_value('lang');

		$infos = $this->configFunctions->get_pages_infos();
		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$menus = Page::menu()->get();

		$contents = $page->content;

		return view('page', ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'menus' => $menus, 'contents' => $contents]);
	}
}
