<?php

namespace App\ControllerFunctions\WebAuth;

use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Illuminate\Support\Facades\Auth;

class VerifyRegistration
{
	public function do($data)
	{
		/**
		 * @var User
		 */
		$user = Auth::user();

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
