<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Responsable;
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
	 * @param AssertedRequest $request
	 *
	 * @return void
	 */
	public function login(AssertedRequest $request): void
	{
		$user = $request->login();
		if ($user === null) {
			throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY);
		}
	}
}
