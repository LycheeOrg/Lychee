<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\WebAuthn;

use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\UnauthenticatedException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnRegisterController extends Controller
{
	/**
	 * Returns a challenge to be verified by the user device.
	 *
	 * @param AttestationRequest $request
	 *
	 * @return Responsable
	 */
	public function options(AttestationRequest $request): Responsable
	{
		/** @disregard P1014 */
		$request->user = Auth::user() ?? throw new UnauthenticatedException();

		return $request
			->fastRegistration()
			->toCreate();
	}

	/**
	 * Registers a device for further WebAuthn authentication.
	 *
	 * @param AttestedRequest $request
	 *
	 * @return void
	 */
	public function register(AttestedRequest $request): void
	{
		/** @disregard P1014 */
		$request->user = Auth::user() ?? throw new UnauthenticatedException();
		$request->save();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);
	}
}
