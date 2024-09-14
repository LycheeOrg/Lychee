<?php

namespace App\View\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
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
	public string $pageTitle;
	public string $pageDescription;
	public string $siteOwner;
	public string $imageUrl;
	public string $pageUrl;
	public bool $rssEnable;
	public string $userCssUrl;
	public string $userJsUrl;
	public string $frame;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		$siteTitle = Configs::getValueAsString('site_title');
		$title = '';
		$description = '';
		$imageUrl = '';

		// if ($this->photoId !== null) {
		// 	$photo = Photo::findOrFail($this->photoId);
		// 	$title = $photo->title;
		// 	$description = $photo->description;
		// 	$imageUrl = url()->to($photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()->url);
		// } elseif ($this->albumId !== null) {
		// 	$albumFactory = resolve(AlbumFactory::class);
		// 	$album = $albumFactory->findAbstractAlbumOrFail($this->albumId, false);
		// 	$title = $album->title;
		// 	$description = $album instanceof BaseAlbum ? $album->description : '';
		// 	$imageUrl = url()->to($album->thumb->thumbUrl ?? '');
		// }

		$this->pageTitle = $siteTitle . (!blank($siteTitle) && !blank($title) ? ' – ' : '') . $title;
		$this->pageDescription = !blank($description) ? $description . ' – via Lychee' : '';
		$this->siteOwner = Configs::getValueAsString('site_owner');
		$this->imageUrl = $imageUrl;
		$this->pageUrl = url()->current();
		$this->rssEnable = Configs::getValueAsBool('rss_enable');
		$this->userCssUrl = self::getUserCustomFiles('user.css');
		$this->userJsUrl = self::getUserCustomFiles('custom.js');
		$this->frame = '';
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