<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelDBException;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use function Safe\phpinfo;

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
		if (Configs::getValueAsBool('landing_page_enable')) {
			$infos = $this->configFunctions->get_pages_infos();

			$title = Configs::getValueAsString('site_title');
			$rss_enable = Configs::getValueAsBool('rss_enable');

			$page_config = [];
			$page_config['show_hosted_by'] = false;
			$page_config['display_socials'] = false;

			return view('landing', [
				'title' => $title,
				'infos' => $infos,
				'page_config' => $page_config,
				'rss_enable' => $rss_enable,
			]);
		}

		return $this->gallery();
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @return void
	 */
	// @codeCoverageIgnoreStart
	public function phpinfo(): void
	{
		Gate::authorize(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);

		phpinfo();
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
		$infos = $this->configFunctions->get_pages_infos();

		$title = Configs::getValueAsString('site_title');
		$rss_enable = Configs::getValueAsBool('rss_enable');
		$page_config = [];
		$page_config['show_hosted_by'] = true;
		$page_config['display_socials'] = Configs::getValueAsBool('display_social_in_gallery');

		return view('gallery', [
			'title' => $title,
			'infos' => $infos,
			'page_config' => $page_config,
			'rss_enable' => $rss_enable,
		]);
	}
}
