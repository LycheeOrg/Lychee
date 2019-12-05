<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Locale\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Response;

class FrameController extends Controller
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

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
	 * @return false|string
	 */
	public function init()
	{
		Configs::get();

		if (Configs::get_value('Mod_Frame') != '1') {
			return redirect()->route('home');
		}

		$lang = Lang::get_lang(Configs::where('key', '=', 'lang')->first());
		$lang['language'] = Configs::get_value('lang');

		$infos = $this->configFunctions->get_pages_infos();
		$title = Configs::get_value('site_title');

		return view('frame', ['locale' => $lang, 'title' => $title, 'infos' => $infos]);
	}

	/**
	 * Return is the refresh rate of the the Frame if it is enabled.
	 *
	 * @return array|string
	 */
	public function getSettings()
	{
		Configs::get();

		if (Configs::get_value('Mod_Frame') != '1') {
			return Response::error('Frame is not enabled');
		}

		$return = [];
		$return['refresh'] = Configs::get_value('Mod_Frame_refresh') * 1000;

		return $return;
	}
}
