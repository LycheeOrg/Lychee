<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Session\LoginRequest;
use App\Http\Resources\Models\UserResource;
use App\Http\Resources\Rights\GlobalRightsResource;
use App\Http\Resources\Root\AuthConfig;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelData\Data;

/**
 * Controller responsible for the authentication of the user.
 */
class AuthController extends Controller
{
	/**
	 * Login tentative.
	 *
	 * @param LoginRequest $request
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function login(LoginRequest $request): void
	{
		if (Auth::attempt([
			'username' => $request->username(),
			'password' => $request->password(),
		])) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' -- User (' . $request->username() . ') has logged in from ' . $request->ip());

			return;
		}

		Log::channel('login')->error(__METHOD__ . ':' . __LINE__ . ' -- User (' . $request->username() . ') has tried to log in from ' . $request->ip());
		throw new UnauthenticatedException('Unknown user or invalid password');
	}

	/**
	 * Unsets the session values.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		Auth::logout();
		Session::flush();
	}

	/**
	 * Get the global rights of the current user.
	 */
	public function getGlobalRights(): Data
	{
		return new GlobalRightsResource();
	}

	/**
	 * First function being called via AJAX.
	 *
	 * @return Data
	 */
	public function getCurrentUser(): Data
	{
		return new UserResource(Auth::user());
	}

	/**
	 * Return the configuration for the authentication.
	 *
	 * @return Data
	 */
	public function getConfig(): Data
	{
		return new AuthConfig();
	}
}
