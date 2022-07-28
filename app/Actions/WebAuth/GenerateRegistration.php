<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Illuminate\Support\Facades\Auth;
use Webauthn\PublicKeyCredentialCreationOptions;

class GenerateRegistration
{
	public function do(): PublicKeyCredentialCreationOptions
	{
		/** @var \App\Models\User */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return WebAuthn::generateAttestation($user);
	}
}
