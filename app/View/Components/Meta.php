<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\View\Components;

use App\Constants\FileSystem;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Models\Album;
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

	public string $page_title;
	public string $page_description;
	public string $site_owner;
	public string $image_url;
	public string $page_url;
	public string $base_url;
	public bool $rss_enable;
	public string $user_css_url;
	public string $user_js_url;

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
		$this->site_owner = request()->configs()->getValueAsString('site_owner');
		$this->page_url = url()->current();
		$this->rss_enable = request()->configs()->getValueAsBool('rss_enable');
		$this->user_css_url = self::getUserCustomFiles('user.css');
		$this->user_js_url = self::getUserCustomFiles('custom.js');

		$base_url = url('/');
		// Work around to try to satisfy everyone...
		// If APP_DIR is set and the url() already contains it, we do not append it a second time.
		if (str_ends_with($base_url, config('app.dir_url'))) {
			$this->base_url = $base_url;
		} else {
			$this->base_url = url(config('app.dir_url') . '/');
		}

		$this->page_title = request()->configs()->getValueAsString('site_title');
		$this->page_description = '';
		$this->image_url = request()->configs()->getValueAsString('landing_background_landscape');

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
			$this->page_title = $this->album->get_title();
			if ($this->album instanceof BaseAlbum) {
				$this->page_description = $this->album->description ?? request()->configs()->getValueAsString('site_title');
			}
			$this->image_url = $this->getHeaderUrl($this->album) ?? $this->image_url;
		}

		if ($this->photo !== null) {
			$this->page_title = $this->photo->title;
			$this->page_description = $this->photo->description ?? request()->configs()->getValueAsString('site_title');
			$this->image_url = $this->photo->size_variants->getMedium()?->url ?? $this->photo->size_variants->getSmall()?->url ?? $this->image_url;
		}
	}

	/**
	 * Render component.
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('components.meta');
	}

	/**
	 * Returns user.css url with cache busting if file has been updated.
	 */
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