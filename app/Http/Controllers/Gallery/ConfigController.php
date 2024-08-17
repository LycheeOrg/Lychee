<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryConfigs\MapProviderData;
use App\Http\Resources\GalleryConfigs\PhotoLayoutConfig;
use App\Http\Resources\GalleryConfigs\UploadConfig;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

/**
 * Controller responsible for the config.
 */
class ConfigController extends Controller
{
	/**
	 * Return gallery layout info.
	 */
	public function getGalleryLayout(): Data
	{
		return new PhotoLayoutConfig();
	}

	public function getMapProvider(): Data
	{
		return new MapProviderData();
	}

	/**
	 * Return the configuration of the uploader.
	 *
	 * @return Data
	 */
	public function getUploadCOnfig(): Data
	{
		return new UploadConfig();
	}
}