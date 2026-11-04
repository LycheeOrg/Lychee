<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\WebAuthn;

use App\Actions\WebAuthn\GenerateRegistrationOptions;
use App\Actions\WebAuthn\StoreCredential;
use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\WebAuthnVerificationException;
use App\Http\Requests\WebAuthn\RegisterCredentialRequest;
use App\Http\Requests\WebAuthn\RegisterOptionsRequest;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class WebAuthnRegisterController extends Controller
{
	/**
	 * Returns a challenge to be verified by the user device (FR-107-01).
	 *
	 * @return array<array-key,mixed>
	 *
	 * @throws UnauthenticatedException
	 */
	public function options(RegisterOptionsRequest $request, GenerateRegistrationOptions $generate): array
	{
		return $generate->do($this->user());
	}

	/**
	 * Registers a device for further WebAuthn authentication (FR-107-03).
	 *
	 * @throws UnauthenticatedException
	 * @throws WebAuthnVerificationException
	 */
	public function register(RegisterCredentialRequest $request, StoreCredential $store): void
	{
		$store->do($this->user(), $request->publicKeyCredential());

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);
	}

	/**
	 * @throws UnauthenticatedException
	 */
	private function user(): User
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user ?? throw new UnauthenticatedException();
	}
}
