<?php

namespace App\Actions\WebAuth;

use App\Exceptions\Internal\InvalidUserIdException;
use App\Exceptions\UnauthenticatedException;
use App\Facades\AccessControl;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

use function Safe\base64_decode;

class VerifyAuthentication
{
	/**
	 * @param string[] $credential
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws InvalidUserIdException
	 */
	public function do(array $credential): void
	{
		$success = WebAuthn::validateAssertion($credential);

		// If is valid, login the user of the credentials.
		if ($success) {
			$user = $this->getUserFromCredentials($credential);
			if ($user !== null) {
				AccessControl::login($user);

				return;
			}
		}
		throw new UnauthenticatedException('Invalid login');
	}

	/**
	 * Return the user that should authenticate via WebAuthn.
	 *
	 * @param string[] $credentials
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
			if ($id !== '') {
				// PHPStan does not understand that `getFromCredentialId` returns `User<Logs>`, but assumes that it returns `WebAuthnAuthenticatable`
				// @phpstan-ignore-next-line
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
		try {
			$result = base64_decode(strtr($rawId, '-_', '+/'), true);
		} catch (\Throwable $e) {
			throw new InvalidUserIdException($e);
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
