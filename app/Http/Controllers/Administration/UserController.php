<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Settings\UpdateLogin;
use App\Actions\User\TokenDisable;
use App\Actions\User\TokenReset;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\User\ChangeLoginRequest;
use App\Http\Requests\User\ChangeTokenRequest;
use App\Http\Requests\User\SetEmailRequest;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 *
	 * @param ChangeLoginRequest $request
	 * @param UpdateLogin        $updateLogin
	 *
	 * @return User
	 */
	public function updateLogin(ChangeLoginRequest $request, UpdateLogin $updateLogin): User
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

		return $currentUser;
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
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Laravel\'s notification module', $e);
		}
	}

	/**
	 * Returns the currently authenticated user or `null` if no user
	 * is authenticated.
	 *
	 * @return User|null
	 */
	public function getAuthenticatedUser(): ?User
	{
		/** @var User|null */
		return Auth::user();
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
		$user = $tokenReset->do();

		return ['token' => $user->token];
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
