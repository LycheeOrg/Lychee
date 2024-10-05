<?php

namespace App\View\Components;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Http\Resources\Traits\HasHeaderUrl;
use App\Models\Configs;
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
	public bool $rssEnable;
	public string $userCssUrl;
	public string $userJsUrl;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		$this->pageTitle = Configs::getValueAsString('site_title');
		$this->pageDescription = '';
		$this->imageUrl = '';

		if (session()->has('album')) {
			/** @var AbstractAlbum $album */
			$album = session()->get('album');
			$this->pageTitle = $album->title;
			$this->pageDescription = $album->description ?? Configs::getValueAsString('site_title');
			$this->imageUrl = $this->getHeaderUrl($album) ?? '';
		}

		if (session()->has('photo')) {
			/** @var Photo $photo */
			$photo = session()->get('photo');
			$this->pageTitle = $photo->title;
			$this->pageDescription = $photo->description ?? Configs::getValueAsString('site_title');
			$this->imageUrl = $photo->size_variants->getSmall()->url;
		}

		$this->siteOwner = Configs::getValueAsString('site_owner');
		$this->pageUrl = url()->current();
		$this->rssEnable = Configs::getValueAsBool('rss_enable');
		$this->userCssUrl = self::getUserCustomFiles('user.css');
		$this->userJsUrl = self::getUserCustomFiles('custom.js');
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