<?php

namespace App\ControllerFunctions\WebAuth;

use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class GenerateAuthentication
{
	public function do($user_id)
	{
		// Find the user to assert, if there is any
		$user = User::where('id', $user_id)->first();

		// Create an assertion for the given user (or a blank one if not found);
		return WebAuthn::generateAssertion($user);
	}
}
