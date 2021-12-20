<?php

namespace App\Http\Controllers;

use App\Facades\Lang;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Page;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class PageController extends Controller
{
	private ConfigFunctions $configFunctions;

	/**
	 * @param ConfigFunctions $configFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions)
	{
		$this->configFunctions = $configFunctions;
	}

	/**
	 * given a URL: http://example.com/<something>
	 * fetches in the tables if the page <something> exists and returns it
	 * return 404 otherwise.
	 *
	 * @param Request $request
	 * @param string  $page
	 *
	 * @return View
	 *
	 * @throws ModelNotFoundException
	 * @throws BindingResolutionException
	 */
	public function page(/* @noinspection PhpUnusedParameterInspection */ Request $request, string $page): View
	{
		/** @var Page $page */
		$page = Page::enabled()->where('link', '=', '/' . $page)->firstOrFail();

		$lang = Lang::get_lang();
		$lang['language'] = Configs::get_value('lang');

		$infos = $this->configFunctions->get_pages_infos();
		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$rss_enable = Configs::get_value('rss_enable', '0') == '1';
		$menus = Page::menu()->get();

		$contents = $page->content;
		$page_config = [];
		$page_config['show_hosted_by'] = false;
		$page_config['display_socials'] = false;

		return view('page', [
			'locale' => $lang,
			'title' => $title,
			'infos' => $infos,
			'menus' => $menus,
			'contents' => $contents,
			'page_config' => $page_config,
			'rss_enable' => $rss_enable,
		]);
	}

	/**
	 * TODO: add function to allow the edition of pages.
	 *
	 * @param Request $request
	 * @param string  $page
	 */
	public function edit(Request $request, string $page): void
	{
	}

	/**
	 * TODO: add function to save the edition of pages.
	 *
	 * @param Request $request
	 * @param string  $page
	 */
	public function save(Request $request, string $page): void
	{
	}
}
