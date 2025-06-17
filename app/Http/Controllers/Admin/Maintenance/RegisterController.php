<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Http\Requests\Maintenance\RegisterRequest;
use App\Http\Resources\GalleryConfigs\RegisterData;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use LycheeVerify\Verify;

class RegisterController extends Controller
{
	/**
	 * Register the Lychee Supporter Edition license key.
	 *
	 * @param RegisterRequest $request
	 *
	 * @return RegisterData
	 */
	public function __invoke(RegisterRequest $request): RegisterData
	{
		Configs::set('license_key', $request->key()->getValue());

		// Verify is a singleton which has already been initialized in RegisterRequest.
		// As a result, we need to reset it after setting the license key.
		app()->instance(Verify::class, null);

		$verify = resolve(Verify::class);
		$is_supporter = $verify->is_supporter();
		if ($is_supporter) {
			// @codeCoverageIgnoreStart
			// Tested locally, not testable in CICD.
			return new RegisterData(true);
			// @codeCoverageIgnoreEnd
		}

		// Not valid, reset the key.
		Configs::set('license_key', '');
		app()->instance(Verify::class, null);

		TaggedRouteCacheUpdated::dispatch(CacheTag::SETTINGS);

		return new RegisterData(false);
	}
}
