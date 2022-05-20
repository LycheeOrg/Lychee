<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthorizedException;
use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

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
		$user = AccessControl::user();

		// okay.
		/** @var false|\Webauthn\PublicKeyCredentialSource */
		$credential = WebAuthn::validateAttestation($data, $user);
		if ($credential != false) {
			$user->addCredential($credential);
		} else {
			throw new UnauthorizedException('Provided credentials are insufficient');
		}
	}
}
