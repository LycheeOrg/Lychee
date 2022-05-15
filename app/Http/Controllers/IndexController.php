<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Facades\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Page;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class IndexController extends Controller
{
	private ConfigFunctions $configFunctions;
	private SymLinkFunctions $symLinkFunctions;

	/**
	 * @param ConfigFunctions  $configFunctions
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions, SymLinkFunctions $symLinkFunctions)
	{
		$this->configFunctions = $configFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
	}

	/**
	 * Display the landing page if enabled
	 * otherwise display the gallery.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 * @throws ModelDBException
	 */
	public function show(): View
	{
		if (Configs::get_value('login_page_enable', '0') == '1' && !AccessControl::is_logged_in()) {
			return view('login', $this->getArrayData(false, false));
		}
		if (Configs::get_value('landing_page_enable', '0') == '1') {
			return view('landing', $this->getArrayData(false, false));
		}

		return $this->gallery();
	}

	/**
	 * Generate the array of data to be passed to the blade template.
	 *
	 * @param bool $showHostedBy
	 * @param bool $displaySocials
	 *
	 * @return array
	 */
	private function getArrayData(bool $showHostedBy, bool $displaySocials): array
	{
		$infos = $this->configFunctions->get_pages_infos();
		$menus = Page::menu()->get();

		$lang = Lang::get_lang();
		$lang['language'] = Configs::get_value('lang');

		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$rss_enable = Configs::get_value('rss_enable', '0') == '1';
		$page_config = [];
		$page_config['show_hosted_by'] = $showHostedBy;
		$page_config['display_socials'] = $displaySocials;

		return [
			'locale' => $lang,
			'title' => $title,
			'infos' => $infos,
			'menus' => $menus,
			'page_config' => $page_config,
			'rss_enable' => $rss_enable,
		];
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @return string
	 */
	// @codeCoverageIgnoreStart
	public function phpinfo(): string
	{
		return (string) phpinfo();
	}
	// @codeCoverageIgnoreEnd

	/**
	 * Display the gallery.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 * @throws ModelDBException
	 */
	public function gallery(): View
	{
		$this->symLinkFunctions->remove_outdated();

		return view('gallery', $this->getArrayData(true, Configs::get_value('display_social_in_gallery', '0') == '1'));
	}
}
