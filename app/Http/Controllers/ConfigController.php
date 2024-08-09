<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalleryConfigs\PhotoLayoutConfig;
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
}