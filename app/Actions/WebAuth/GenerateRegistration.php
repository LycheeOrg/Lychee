<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Webauthn\PublicKeyCredentialCreationOptions;

class GenerateRegistration
{
	public function do(): PublicKeyCredentialCreationOptions
	{
		return WebAuthn::generateAttestation(AccessControl::user());
	}
}
