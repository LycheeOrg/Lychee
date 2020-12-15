<?php

namespace App\ControllerFunctions\WebAuth;

use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Illuminate\Support\Facades\Auth;

class GenerateRegistration
{
	public function do()
	{
		/**
		 * @var User
		 */
		$user = Auth::user();

		// Create an attestation for a given user.
		return WebAuthn::generateAttestation($user);
	}
}
