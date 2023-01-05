<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidation;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidator;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WebAuthnLoginController
{
	/**
	 * Returns the challenge to assertion.
	 *
	 * @param AssertionRequest $request
	 *
	 * @return Responsable
	 *
	 * @throws BindingResolutionException
	 */
	public function options(AssertionRequest $request): Responsable
	{
		return $request->toVerify($request->validate(['user_id' => 'sometimes|int'])['user_id']);
	}

	/**
	 * Log the user in.
	 *
	 * 1. We retrieve the credentials candidate
	 * 2. Double check the challenge is signed.
	 * 3. Retrieve the User from the credential ID, we will use it to validate later (otherwise keys like yubikey4 are not working).
	 * 4. Validate the credentials
	 * 5. Log in on success
	 *
	 * @param AssertedRequest $request
	 *
	 * @return void
	 */
	public function login(AssertedRequest $request, AssertionValidator $validator): void
	{
		$credentials = $request->validated();

		if (!$this->isSignedChallenge($credentials)) {
			throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Response is not signed.');
		}
		$associatedUser = $this->retrieveByCredentials($credentials);

		if ($associatedUser === null) {
			throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Associated user does not exists.');
		}

		$credential = $validator
			->send(new AssertionValidation($request, $associatedUser))
			->thenReturn()
			->credential;

		if ($credential === null) {
			throw new UnauthenticatedException('Invalid credentials');
		}

		/** @var \Illuminate\Contracts\Auth\Authenticatable $authenticatable */
		$authenticatable = $credential->authenticatable;
		Auth::login($authenticatable);
	}

	/**
	 * Check if the credentials are for a public key signed challenge.
	 *
	 * @param array $credentials
	 *
	 * @return bool
	 */
	private function isSignedChallenge(array $credentials): bool
	{
		return isset($credentials['id'], $credentials['rawId'], $credentials['response'], $credentials['type']);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return User|null
	 */
	public function retrieveByCredentials(array $credentials): User|null
	{
		/** @var User|null $user */
		$user = User::whereHas('webAuthnCredentials',
			fn ($query) => $query->whereKey($credentials['id'])->whereEnabled()
		)->first();

		return $user;
	}
}
