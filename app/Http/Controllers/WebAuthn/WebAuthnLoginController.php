<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use App\Pipelines\AssertionValidator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidation;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use function response;

class WebAuthnLoginController
{
	/**
	 * Returns the challenge to assertion.
	 *
	 * @param AssertionRequest $request
	 *
	 * @return \Illuminate\Contracts\Support\Responsable
	 */
	public function options(AssertionRequest $request): Responsable
	{
		return $request->toVerify($request->validate(['user_id' => 'sometimes|int'])['user_id']);
	}

	/**
	 * Log the user in.
	 *
	 * @param AssertedRequest $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function login(AssertedRequest $request, AssertionValidator $assertion): Response
	{
		$credential = $assertion
		->send(new AssertionValidation($request, User::findOrFail(0)))
		->thenReturn()->credential;

		if ($credential !== null) {
			/** @var \Illuminate\Contracts\Auth\Authenticatable $authenticatable */
			$authenticatable = $credential->authenticatable;
			Auth::login($authenticatable);
		}

		return response()->noContent($credential !== null ? 204 : throw new UnauthenticatedException('Invalid credentials'));
	}
}
