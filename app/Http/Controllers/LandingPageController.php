<?php

namespace App\Http\Controllers;

use App\Http\Resources\GalleryConfigs\LandingPageResource;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the data displayed on the landing page.
 */
class LandingPageController extends Controller
{
	public function __invoke(): LandingPageResource
	{
		return new LandingPageResource();
	}
}
