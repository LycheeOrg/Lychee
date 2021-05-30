<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class GenerateRegistration
{
	public function do()
	{
		/**
		 * @var User
		 */
		$user = AccessControl::user();

		// Create an attestation for a given user.
		return WebAuthn::generateAttestation($user);
	}
}
