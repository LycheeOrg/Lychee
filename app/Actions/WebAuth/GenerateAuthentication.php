<?php

namespace App\Actions\WebAuth;

use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Webauthn\PublicKeyCredentialRequestOptions;

class GenerateAuthentication
{
	public function do($user_id): PublicKeyCredentialRequestOptions
	{
		/** @var User $user */
		$user = User::query()->where('id', $user_id)->first();

		return WebAuthn::generateAssertion($user);
	}
}
