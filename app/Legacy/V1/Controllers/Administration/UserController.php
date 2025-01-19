<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers\Administration;

use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Legacy\Actions\Settings\UpdateLogin;
use App\Legacy\V1\Requests\User\ChangeLoginRequest;
use App\Legacy\V1\Requests\User\ChangeTokenRequest;
use App\Legacy\V1\Requests\User\SetEmailRequest;
use App\Legacy\V1\Resources\Models\UserResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class UserController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 *
	 * @param ChangeLoginRequest $request
	 * @param UpdateLogin        $updateLogin
	 *
	 * @return UserResource
	 */
	public function updateLogin(ChangeLoginRequest $request, UpdateLogin $updateLogin): UserResource
	{
		$currentUser = $updateLogin->do(
			$request->username(),
			$request->password(),
			$request->oldPassword(),
			$request->ip()
		);
		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($currentUser);

		return UserResource::make($currentUser);
	}

	/**
	 * Updates the email address of the currently authenticated user.
	 * Deletes all notifications if the email address is empty.
	 *
	 * TODO: Why is this an independent request? IMHO this should be combined with the other user settings.
	 *
	 * @param SetEmailRequest $request
	 *
	 * @return void
	 *
	 * @throws InternalLycheeException
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function setEmail(SetEmailRequest $request): void
	{
		try {
			/** @var User $user */
			$user = Auth::user() ?? throw new UnauthenticatedException();

			$user->email = $request->email();

			if ($request->email() === null) {
				$user->notifications()->delete();
			}

			$user->save();
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Laravel\'s notification module', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the currently authenticated user or `null` if no user
	 * is authenticated.
	 *
	 * @return UserResource
	 */
	public function getAuthenticatedUser(): UserResource
	{
		return UserResource::make(Auth::user() ?? throw new UnauthenticatedException());
	}

	/**
	 * Reset the token of the currently authenticated user.
	 *
	 * @return array{'token': string}
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 * @throws \Exception
	 */
	public function resetToken(ChangeTokenRequest $request, TokenReset $tokenReset): array
	{
		$token = $tokenReset->do();

		return ['token' => $token];
	}

	/**
	 * Disable the token of the currently authenticated user.
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function unsetToken(ChangeTokenRequest $request, TokenDisable $tokenDisable): void
	{
		$tokenDisable->do();
	}
}
