<?php

namespace App\Actions\WebAuth;

use App\Auth\Authorization;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Webauthn\PublicKeyCredentialCreationOptions;

class GenerateRegistration
{
	public function do(): PublicKeyCredentialCreationOptions
	{
		return WebAuthn::generateAttestation(Authorization::user());
	}
}
