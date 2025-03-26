<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Profile\UpdateLogin;
use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Profile\ChangeTokenRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Models\UserResource;
use App\Http\Resources\Models\Utils\UserToken;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 */
	public function update(UpdateProfileRequest $request, UpdateLogin $update_login): UserResource
	{
		/** @var User $current_user */
		$current_user = Auth::user();

		if ($request->username() !== null &&
			$request->username() !== '' &&
			Configs::getValueAsBool('allow_username_change')) {
			$update_login->updateUsername($current_user, $request->username(), $request->ip());
		}

		$current_user = $update_login->updatePassword(
			$current_user,
			$request->password()
		);

		$current_user = $update_login->updateEmail(
			$current_user,
			$request->email()
		);

		$current_user->save();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);

		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($current_user);

		return new UserResource($current_user);
	}

	/**
	 * Reset the token of the currently authenticated user.
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 * @throws \Exception
	 */
	public function resetToken(ChangeTokenRequest $request, TokenReset $token_reset): UserToken
	{
		$token = $token_reset->do();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);

		return new UserToken($token);
	}

	/**
	 * Disable the token of the currently authenticated user.
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function unsetToken(ChangeTokenRequest $request, TokenDisable $token_disable): void
	{
		$token_disable->do();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);
	}
}