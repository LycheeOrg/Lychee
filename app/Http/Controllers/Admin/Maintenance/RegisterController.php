<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Maintenance\RegisterRequest;
use App\Http\Resources\GalleryConfigs\RegisterData;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use LycheeVerify\Contract\VerifyInterface;
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

		/** @var Verify|VerifyInterface $verify */
		$verify = $request->verify();

		if (!$verify instanceof Verify) {
			// This should never happen, but let's be safe.
			throw new LycheeLogicException('Verify instance could not be resolved.');
		}

		$verify->reset_status();
		$is_supporter = $verify->is_supporter();
		if ($is_supporter) {
			// @codeCoverageIgnoreStart
			// Tested locally, not testable in CICD.
			return new RegisterData(true);
			// @codeCoverageIgnoreEnd
		}

		// Not valid, reset the key.
		Configs::set('license_key', '');
		$verify->reset_status();

		TaggedRouteCacheUpdated::dispatch(CacheTag::SETTINGS);

		return new RegisterData(false);
	}
}
