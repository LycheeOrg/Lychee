<?php

namespace App\Http\Controllers;

use App\Contracts\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\FrameworkException;
use App\Facades\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class FrameController extends Controller
{
	private ConfigFunctions $configFunctions;

	/**
	 * FrameController constructor.
	 *
	 * @param ConfigFunctions $configFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions)
	{
		$this->configFunctions = $configFunctions;
	}

	/**
	 * Return the page /frame if enabled.
	 *
	 * @return RedirectResponse|View
	 *
	 * @throws LycheeException
	 */
	public function init(): RedirectResponse|View
	{
		try {
			Configs::get();

			if (Configs::getValueAsBool('Mod_Frame', false) == false) {
				return redirect()->route('home');
			}

			$lang = Lang::get_lang();
			$lang['language'] = Configs::getValueAsString('lang');

			$infos = $this->configFunctions->get_pages_infos();
			$title = Configs::getValueAsString('site_title');

			return view('frame', ['locale' => $lang, 'title' => $title, 'infos' => $infos, 'rss_enable' => false]);
		} catch (BindingResolutionException|RouteNotFoundException $e) {
			throw new FrameworkException('Laravel\'s redirect or render component', $e);
		}
	}

	/**
	 * Return is the refresh rate of the Frame if it is enabled.
	 *
	 * @return array
	 *
	 * @throws LycheeException
	 */
	public function getSettings(): array
	{
		Configs::get();

		if (Configs::getValueAsBool('Mod_Frame', false) == false) {
			throw new ConfigurationException('Frame is not enabled');
		}

		$return = [];
		$return['refresh'] = Configs::getValueAsInt('Mod_Frame_refresh') * 1000;

		return $return;
	}
}
