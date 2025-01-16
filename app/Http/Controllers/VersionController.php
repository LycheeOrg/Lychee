<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Http\Resources\Root\VersionResource;
use Illuminate\Routing\Controller;

class VersionController extends Controller
{
	/**
	 * Retrieve the data about updates (so that it is not fully blocking).
	 *
	 * @return VersionResource
	 */
	public function get(): VersionResource
	{
		return new VersionResource();
	}
}
