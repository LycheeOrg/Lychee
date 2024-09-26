<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalleryConfigs\LandingPageResource;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

/**
 * Controller responsible for the data displayed on the landing page.
 */
class LandingPageController extends Controller
{
	public function __invoke(): Data
	{
		return new LandingPageResource();
	}
}
