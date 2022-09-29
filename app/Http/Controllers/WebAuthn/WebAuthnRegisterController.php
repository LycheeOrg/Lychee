<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnRegisterController
{
	/**
	 * Returns a challenge to be verified by the user device.
	 *
	 * @param \Laragear\WebAuthn\Http\Requests\AttestationRequest $request
	 *
	 * @return \Illuminate\Contracts\Support\Responsable
	 */
	public function options(AttestationRequest $request): Responsable
	{
		$request->user = Auth::user() ?? throw new UnauthenticatedException();

		return $request
			->fastRegistration()
			->toCreate();
	}

	/**
	 * Registers a device for further WebAuthn authentication.
	 *
	 * @param \Laragear\WebAuthn\Http\Requests\AttestedRequest $request
	 *
	 * @return void
	 */
	public function register(AttestedRequest $request): void
	{
		$request->user = Auth::user() ?? throw new UnauthenticatedException();
		$request->save();
	}
}
