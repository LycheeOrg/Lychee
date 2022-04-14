<?php

namespace App\Actions\WebAuth;

use App\Exceptions\Internal\InvalidUserIdException;
use App\Exceptions\UnauthenticatedException;
use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class VerifyAuthentication
{
	/**
	 * @throws UnauthenticatedException
	 * @throws InvalidUserIdException
	 */
	public function do($credential): void
	{
		$success = WebAuthn::validateAssertion($credential);

		// If is valid, login the user of the credentials.
		if ($success) {
			$user = $this->getUserFromCredentials($credential);
			if ($user) {
				AccessControl::login($user);

				return;
			}
		}
		throw new UnauthenticatedException('Invalid login');
	}

	/**
	 * Return the user that should authenticate via WebAuthn.
	 *
	 * @param array $credentials
	 *
	 * @return User|null
	 *
	 * @throws InvalidUserIdException
	 */
	protected function getUserFromCredentials(array $credentials): ?User
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
	 * @return string
	 *
	 * @throws InvalidUserIdException
	 */
	protected function binaryID(string $rawId): string
	{
		$result = base64_decode(strtr($rawId, '-_', '+/'), true);
		if ($result === false) {
			throw new InvalidUserIdException();
		}

		return $result;
	}

	/**
	 * Check if the credentials are for a public key signed challenge.
	 *
	 * @param array $credentials
	 *
	 * @return bool
	 */
	protected function isSignedChallenge(array $credentials): bool
	{
		return isset($credentials['id'], $credentials['rawId'], $credentials['type'], $credentials['response']);
	}
}
