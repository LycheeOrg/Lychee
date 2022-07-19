<?php

namespace App\Actions\WebAuth;

use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Illuminate\Support\Facades\Auth;
use Webauthn\PublicKeyCredentialCreationOptions;

class GenerateRegistration
{
	public function do(): PublicKeyCredentialCreationOptions
	{
		/** @var \App\Models\User */
		$user = Auth::authenticate();

		return WebAuthn::generateAttestation($user);
	}
}
