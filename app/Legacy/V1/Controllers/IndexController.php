<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Constants\FileSystem;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Legacy\V1\Requests\View\GetPhotoViewRequest;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use function Safe\file_get_contents;
use function Safe\phpinfo;

final class IndexController extends Controller
{
	/**
	 * @param SymLinkFunctions $sym_link_functions
	 */
	public function __construct(
		private SymLinkFunctions $sym_link_functions)
	{
	}

	/**
	 * Display the landing page if enabled
	 * otherwise display the gallery.
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws ConfigurationKeyMissingException
	 */
	public function show(): View
	{
		try {
			if (Configs::getValueAsBool('landing_page_enable')) {
				$infos = [
					'owner' => Configs::getValueAsString('site_owner'),
					'title' => Configs::getValueAsString('landing_title'),
					'subtitle' => Configs::getValueAsString('landing_subtitle'),
					'facebook' => Configs::getValueAsString('sm_facebook_url'),
					'flickr' => Configs::getValueAsString('sm_flickr_url'),
					'twitter' => Configs::getValueAsString('sm_twitter_url'),
					'instagram' => Configs::getValueAsString('sm_instagram_url'),
					'youtube' => Configs::getValueAsString('sm_youtube_url'),
					'background' => Configs::getValueAsString('landing_background_landscape'),
					'copyright_enable' => Configs::getValueAsString('footer_show_copyright'),
					'copyright_year' => Configs::getValueAsString('site_copyright_begin'),
					'additional_footer_text' => Configs::getValueAsString('footer_additional_text'),
				];
				if (Configs::getValueAsString('site_copyright_begin') !== Configs::getValueAsString('site_copyright_end')) {
					// @codeCoverageIgnoreStart
					$infos['copyright_year'] = Configs::getValueAsString('site_copyright_begin') . '-' . Configs::getValueAsString('site_copyright_end');
					// @codeCoverageIgnoreEnd
				}

				$title = Configs::getValueAsString('site_title');
				$rss_enable = Configs::getValueAsBool('rss_enable');

				$page_config = [];
				$page_config['show_hosted_by'] = false;
				$page_config['display_socials'] = true;

				return view('landing', [
					'title' => $title,
					'infos' => $infos,
					'page_config' => $page_config,
					'rss_enable' => $rss_enable,
					'user_css_url' => self::getUserCustomFiles('user.css'),
					'user_js_url' => self::getUserCustomFiles('custom.js'),
				]);
			}

			return $this->frontend();
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function phpinfo(): void
	{
		Gate::authorize(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);

		phpinfo();
	}

	/**
	 * Returns the frontend in "gallery mode".
	 *
	 * This is an alias for {@link IndexController::frontend()} with default
	 * parameters.
	 *
	 * @deprecated
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws ConfigurationKeyMissingException
	 */
	public function gallery(): View
	{
		return $this->frontend();
	}

	/**
	 * Returns the frontend in "frame mode".
	 *
	 * This is an alias for {@link IndexController::frontend()} with default
	 * parameters.
	 * This method can be removed as soon as the frontend fully supports
	 * client-side navigation with a proper path component in the URL
	 * instead of using URL fragments.
	 * See: https://github.com/LycheeOrg/Lychee-front/issues/343
	 * Until then, this method allows us to use `/frame` as the URL path
	 * by catching this URL on the server-side and returning the frontend.
	 *
	 * @deprecated
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws ConfigurationKeyMissingException
	 *
	 * @codeCoverageIgnore
	 */
	public function frame(): View
	{
		return $this->frontend();
	}

	/**
	 * Returns the frontend in "view mode".
	 *
	 * The view mode is used to display a single photo in a search engine
	 * and social media friendly way.
	 * This method can be removed as soon as the frontend fully supports
	 * client-side navigation with a proper path component in the URL
	 * instead of using URL fragments.
	 * See: https://github.com/LycheeOrg/Lychee-front/issues/343
	 * Until then, this method allows us to use `/view` as the URL path
	 * by catching this URL on the server-side and returning the frontend.
	 *
	 * @deprecated
	 *
	 * @param GetPhotoViewRequest $request
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws ConfigurationKeyMissingException
	 * @throws ModelDBException
	 *
	 * @codeCoverageIgnore
	 */
	public function view(GetPhotoViewRequest $request): View
	{
		$photo = $request->photo();

		return $this->frontend(
			$photo->title,
			$photo->description,
			url()->to($photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url)
		);
	}

	/**
	 * Returns the frontend with pre-rendered meta tags in the HTML header.
	 *
	 * @param string|null $title       the specific title; this method prefixes the title with the site title
	 * @param string|null $description the description; this method appends `' – via Lychee'` to the description
	 * @param string|null $image_url   an optional URL to an image displayed on the page
	 *
	 * @throws FrameworkException
	 * @throws ConfigurationKeyMissingException
	 * @throws ModelDBException
	 */
	protected function frontend(?string $title = null, ?string $description = null, ?string $image_url = null): View
	{
		try {
			$this->sym_link_functions->remove_outdated();
			$site_title = Configs::getValueAsString('site_title');
			$title ??= '';
			$description ??= '';

			return view('frontend', [
				'pageTitle' => $site_title . ($site_title !== '' && $title !== '' ? ' – ' : '') . $title,
				'pageDescription' => $description !== '' ? $description . ' – via Lychee' : '',
				'siteOwner' => Configs::getValueAsString('site_owner'),
				'imageUrl' => $image_url ?? '',
				'pageUrl' => url()->current(),
				'rssEnable' => Configs::getValueAsBool('rss_enable'),
				'bodyHtml' => file_get_contents(public_path('dist/frontend.html')),
				'userCssUrl' => self::getUserCustomFiles('user.css'),
				'userJsUrl' => self::getUserCustomFiles('custom.js'),
			]);
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns user.css url with cache busting if file has been updated.
	 * */
	public static function getUserCustomFiles(string $file_name): string
	{
		$css_cache_busting = '';
		/** @disregard P1013 */
		if (Storage::disk(FileSystem::DIST)->fileExists($file_name)) {
			$css_cache_busting = '?' . Storage::disk(FileSystem::DIST)->lastModified($file_name);
		}

		/** @disregard P1013 */
		return Storage::disk(FileSystem::DIST)->url($file_name) . $css_cache_busting;
	}
}