<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class VerifyAuthentication
{
	public function do($credential)
	{
		$cred = WebAuthn::validateAssertion($credential);

		// If is valid, login the user of the credentials.
		if ($cred) {
			$user = $this->getUserFromCredentials($credential);
			if ($user) {
				AccessControl::login($user);

				return response()->json('Authenticated!', 200);
			}
		}

		return response()->json('Something went wrong with your device!', 422);
	}

	/**
	 * Return the user that should authenticate via WebAuthn.
	 *
	 * @param array $credentials
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|\DarkGhostHunter\Larapass\Contracts\WebAuthnAuthenticatable|null
	 */
	protected function getUserFromCredentials(array $credentials)
	{
		// We will try to ask the User Provider for any user for the given credentials.
		// If there is one, we will then return an array of credentials ID that the
		// authenticator may use to sign the subsequent challenge by the server.
		if ($this->isSignedChallenge($credentials)) {
			$id = $this->binaryID($credentials['rawId']);
			if ($id) {
				return User::getFromCredentialId($id);
			}
		}

		return null;
	}

	/**
	 * Transforms the raw ID string into a binary string.
	 *
	 * @param string $rawId
	 *
	 * @return string|null
	 */
	protected function binaryID(string $rawId)
	{
		return base64_decode(strtr($rawId, '-_', '+/'), true);
	}

	/**
	 * Check if the credentials are for a public key signed challenge.
	 *
	 * @param array $credentials
	 *
	 * @return bool
	 */
	protected function isSignedChallenge(array $credentials)
	{
		return isset($credentials['id'], $credentials['rawId'], $credentials['type'], $credentials['response']);
	}
}
