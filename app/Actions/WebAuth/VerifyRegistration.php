<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class VerifyRegistration
{
	public function do($data)
	{
		/**
		 * @var User
		 */
		$user = AccessControl::user();

		// okay.
		$credential = WebAuthn::validateAttestation($data, $user);
		if ($credential) {
			$user->addCredential($credential);

			return response()->json('Device registered!', 200);
		} else {
			return response()->json('Something went wrong with your device!', 422);
		}
	}
}
