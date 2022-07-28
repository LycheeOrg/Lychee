<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use Illuminate\Support\Facades\Auth;

class VerifyRegistration
{
	/**
	 * @throws UnauthorizedException
	 */
	public function do(array $data): void
	{
		/**
		 * @var User
		 */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		// okay.
		/** @var false|\Webauthn\PublicKeyCredentialSource */
		$credential = WebAuthn::validateAttestation($data, $user);
		if ($credential !== false) {
			$user->addCredential($credential);
		} else {
			throw new UnauthorizedException('Provided credentials are insufficient');
		}
	}
}
