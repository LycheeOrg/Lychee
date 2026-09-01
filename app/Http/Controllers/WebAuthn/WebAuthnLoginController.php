<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use App\Exceptions\WebAuthnDisabledExecption;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidation;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidator;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laragear\WebAuthn\JsonTransport;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WebAuthnLoginController extends Controller
{
	/**
	 * Returns the challenge to assertion.
	 *
	 * @throws BindingResolutionException
	 */
	public function options(AssertionRequest $request): Responsable
	{
		$this->checkEnabled();

		/** @phpstan-ignore staticMethod.dynamicCall */
		$fields = $request->validate([
			'user_id' => 'sometimes|int',
			'username' => 'sometimes|string',
		]);

		$username = $fields['username'] ?? null;
		$user_id = $fields['user_id'] ?? null;
		$authenticatable = $user_id ?? ($username !== null ? ['username' => $username] : null);

		// If the user does not exist, we generate a fake response to prevent user enumeration.
		// If there was no username and no user_id, this means that we are dealing quite likely with
		// a Yubikey 5.
		//
		// Yubikey 4 requires the use of username or passwords.
		if (
			($username !== null || $user_id !== null)
			&& !User::where('id', $user_id)->orWhere('username', $username)->exists()
		) {
			return $this->generateFakeResponse();
		}

		return $request->toVerify($authenticatable);
	}

	/**
	 * We mask real responses with fake ones to prevent user enumeration.
	 */
	private function generateFakeResponse(): Responsable
	{
		return new class implements Responsable {
			function toResponse($request): \Symfony\Component\HttpFoundation\Response {
				$count = random_int(1, 3);
				$credentials = [];
				for ($i = 0; $i < $count; $i++) {
					$credentials[] = [
						"id" => strtr(base64_encode(random_bytes(96)), '+/', '-_'),
						"type" => "public-key",
					];
				}

				return response()->json([
					"timeout" => 60000,
					"allowCredentials" => $credentials,
					"challenge" => strtr(base64_encode(random_bytes(18)), '+/', '-_'),
				]);
			}
		};
	}

	/**
	 * Log the user in.
	 *
	 * 1. We retrieve the credentials candidate
	 * 2. Double check the challenge is signed.
	 * 3. Retrieve the User from the credential ID, we will use it to validate later (otherwise keys like yubikey4 are not working).
	 * 4. Validate the credentials
	 * 5. Log in on success
	 */
	public function login(AssertedRequest $request, AssertionValidator $validator): void
	{
		$this->checkEnabled();

		$credentials = $request->validated();

		if (!$this->isSignedChallenge($credentials)) {
			// @codeCoverageIgnoreStart
			throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Response is not signed.');
			// @codeCoverageIgnoreEnd
		}
		$associated_user = $this->retrieveByCredentials($credentials);

		if ($associated_user === null) {
			// @codeCoverageIgnoreStart
			throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Associated user does not exists.');
			// @codeCoverageIgnoreEnd
		}

		$json_transport = new JsonTransport($request->only(AssertionValidation::REQUEST_KEYS));

		$credential = $validator
			->send(new AssertionValidation($json_transport, $associated_user))
			->thenReturn()
			->credential;

		if ($credential === null) {
			// @codeCoverageIgnoreStart
			throw new UnauthenticatedException('Invalid credentials');
			// @codeCoverageIgnoreEnd
		}

		$authenticatable = $credential->authenticatable;
		Auth::login($authenticatable);
	}

	/**
	 * Check if the credentials are for a public key signed challenge.
	 *
	 * @param array<string,string> $credentials
	 */
	private function isSignedChallenge(#[\SensitiveParameter] array $credentials): bool
	{
		return isset($credentials['id'], $credentials['rawId'], $credentials['response'], $credentials['type']);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param array<string,string> $credentials
	 */
	public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): User|null
	{
		/** @var User|null $user */
		$user = User::whereHas('webAuthnCredentials',
			fn ($query) => $query->where('id', '=', $credentials['id'])->whereNull('disabled_at')
		)->first();

		return $user;
	}

	/**
	 * Validate whether the WebAuthn is enabled in the configuration.
	 * If not throw an exception with status code 403 (Forbidden).
	 *
	 * @return void
	 *
	 * @throws WebAuthnDisabledExecption
	 */
	private function checkEnabled(): void
	{
		if (AuthServiceProvider::isWebAuthnEnabled() === false) {
			throw new WebAuthnDisabledExecption();
		}
	}
}