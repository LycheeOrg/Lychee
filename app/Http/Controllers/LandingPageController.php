<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
