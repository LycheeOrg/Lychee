<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\View\Components;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * This is the bottom of the page.
 * We provides socials etc...
 */
class Meta extends Component
{
	use HasHeaderUrl;

	public string $pageTitle;
	public string $pageDescription;
	public string $siteOwner;
	public string $imageUrl;
	public string $pageUrl;
	public string $baseUrl;
	public bool $rssEnable;
	public string $userCssUrl;
	public string $userJsUrl;

	private bool $access = true;
	private ?AbstractAlbum $album = null;
	private ?Photo $photo = null;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		// default data
		$this->siteOwner = Configs::getValueAsString('site_owner');
		$this->pageUrl = url()->current();
		$this->rssEnable = Configs::getValueAsBool('rss_enable');
		$this->userCssUrl = self::getUserCustomFiles('user.css');
		$this->userJsUrl = self::getUserCustomFiles('custom.js');
		$this->baseUrl = url('/');

		$this->pageTitle = Configs::getValueAsString('site_title');
		$this->pageDescription = '';
		$this->imageUrl = Configs::getValueAsString('landing_background');

		// processing photo and album data
		if (session()->has('access')) {
			$this->access = session()->get('access');
			session()->forget('access');
		}
		if (session()->has('album')) {
			$this->album = session()->get('album');
			session()->forget('album');
		}
		if (session()->has('photo')) {
			$this->photo = session()->get('photo');
			session()->forget('photo');
		}

		if ($this->access === false) {
			return;
		}

		if ($this->album !== null) {
			$this->pageTitle = $this->album->title;
			if ($this->album instanceof BaseAlbum) {
				$this->pageDescription = $this->album->description ?? Configs::getValueAsString('site_title');
			}
			$this->imageUrl = $this->getHeaderUrl($this->album) ?? $this->imageUrl;
		}

		if ($this->photo !== null) {
			$this->pageTitle = $this->photo->title;
			$this->pageDescription = $this->photo->description ?? Configs::getValueAsString('site_title');
			$this->imageUrl = $this->photo->size_variants->getSmall()->url;
		}
	}

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('components.meta');
	}

	/**
	 * Returns user.css url with cache busting if file has been updated.
	 *
	 * @param string $fileName
	 *
	 * @return string
	 */
	public static function getUserCustomFiles(string $fileName): string
	{
		$cssCacheBusting = '';
		/** @disregard P1013 */
		if (Storage::disk('dist')->fileExists($fileName)) {
			$cssCacheBusting = '?' . Storage::disk('dist')->lastModified($fileName);
		}

		/** @disregard P1013 */
		return Storage::disk('dist')->url($fileName) . $cssCacheBusting;
	}
}