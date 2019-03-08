<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Page;

class IndexController extends Controller
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


	public function show()
	{
		Configs::get();

		if (Configs::get_value('landing_page_enable', '0') == '1')
		{
			$lang = Lang::get_lang(Configs::get_value('lang', 'en'));

			$infos = $this->configFunctions->get_pages_infos();

			$menus = Page::menu()->get();

			$title = Configs::get_value('site_title', 'Lychee v4');


			return view('landing', ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'menus' => $menus]);
		}

		return $this->gallery();
	}


	public function phpinfo()
	{
		return (string) phpinfo();
	}

	public function gallery()
	{
		Configs::get();

		$lang = Lang::get_lang(Configs::get_value('lang', 'en'));
		$title = Configs::get_value('site_title', 'Lychee v4');

		return view('gallery', ['locale' => $lang, 'title' => $title]);
	}
}
