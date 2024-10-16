<?php

namespace App\Http\Controllers;

use App\Actions\Profile\UpdateLogin;
use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Profile\ChangeTokenRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Models\UserResource;
use App\Http\Resources\Models\Utils\UserToken;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 *
	 * @param UpdateProfileRequest $request
	 * @param UpdateLogin          $updateLogin
	 *
	 * @return UserResource
	 */
	public function update(UpdateProfileRequest $request, UpdateLogin $updateLogin): UserResource
	{
		/** @var User $currentUser */
		$currentUser = Auth::user();

		$currentUser = $updateLogin->updateUsername(
			$currentUser,
			$request->username(),
			$request->ip()
		);

		$currentUser = $updateLogin->updatePassword(
			$currentUser,
			$request->password()
		);

		$currentUser = $updateLogin->updateEmail(
			$currentUser,
			$request->email()
		);

		$currentUser->save();
		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($currentUser);

		return new UserResource($currentUser);
	}

	/**
	 * Reset the token of the currently authenticated user.
	 *
	 * @return UserToken
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 * @throws \Exception
	 */
	public function resetToken(ChangeTokenRequest $request, TokenReset $tokenReset): UserToken
	{
		$token = $tokenReset->do();

		return new UserToken($token);
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
