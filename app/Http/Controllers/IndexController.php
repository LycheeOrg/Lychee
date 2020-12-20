<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Locale\Lang;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Page;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class IndexController extends Controller
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @param ConfigFunctions  $configFunctions
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions, SymLinkFunctions $symLinkFunctions, LycheeVersion $lycheeVersion)
	{
		$this->configFunctions = $configFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->lycheeVersion = $lycheeVersion;
	}

	/**
	 * Display the landing page if enabled
	 * otherwise display the gallery.
	 *
	 * @return View
	 */
	public function show()
	{
		if (Configs::get_value('landing_page_enable', '0') == '1') {
			$lang = Lang::get_lang(Configs::get_value('lang'));
			$lang['language'] = Configs::get_value('lang');

			$infos = $this->configFunctions->get_pages_infos();

			$menus = Page::menu()->get();

			$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
			$rss_enable = (Configs::get_value('rss_enable', '0') == '1') ? true : false;

			$page_config = [];
			$page_config['show_hosted_by'] = false;
			$page_config['display_socials'] = false;

			return view('landing', ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'menus' => $menus, 'page_config' => $page_config, 'rss_enable' => $rss_enable]);
		}

		return $this->gallery();
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @return string
	 */
	// @codeCoverageIgnoreStart
	public function phpinfo()
	{
		return (string) phpinfo();
	}

	// @codeCoverageIgnoreEnd

	/**
	 * Display the gallery.
	 *
	 * @return View
	 */
	public function gallery()
	{
		$this->symLinkFunctions->remove_outdated();
		$infos = $this->configFunctions->get_pages_infos();

		$lang = Lang::get_lang(Configs::get_value('lang'));
		$lang['language'] = Configs::get_value('lang');

		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$rss_enable = (Configs::get_value('rss_enable', '0') == '1') ? true : false;
		$page_config = [];
		$page_config['show_hosted_by'] = true;
		$page_config['display_socials'] = Configs::get_value('display_social_in_gallery', '0') == '1';

		return view('gallery', ['locale' => $lang, 'title' => $title, 'infos' => $infos,  'page_config' => $page_config, 'rss_enable' => $rss_enable]);
	}
}
