<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Page;
use Illuminate\Support\Facades\Config;

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

		if (Configs::get_value('landing_page_enable', '0') == '1')
		{
			$lang = Lang::get_lang(Configs::get_value('lang'));
			$lang['language'] = Configs::get_value('lang');

			$infos = $this->configFunctions->get_pages_infos();

			$menus = Page::menu()->get();

			$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));

			$page_config = array();
			$page_config['show_hosted_by'] = false;
			$page_config['display_socials'] = false;

			return view('landing', ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'menus' => $menus, 'page_config'=> $page_config]);
		}

		return $this->gallery();
	}


	public function phpinfo()
	{
		return (string) phpinfo();
	}

	public function gallery()
	{

		$infos = $this->configFunctions->get_pages_infos();

		$lang = Lang::get_lang(Configs::get_value('lang'));
		$lang['language'] = Configs::get_value('lang');

		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$page_config = array();
		$page_config['show_hosted_by'] = true;
		$page_config['display_socials'] = Configs::get_value('display_social_in_gallery','0') == '1';

		return view('gallery', ['locale' => $lang, 'title' => $title, 'infos' => $infos,  'page_config'=> $page_config]);
	}
}
