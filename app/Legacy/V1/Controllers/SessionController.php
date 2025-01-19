<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\VersionControlException;
use App\Http\Requests\Session\LoginRequest;
use App\Legacy\AdminAuthentication;
use App\Legacy\V1\Resources\InitResource;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

final class SessionController extends Controller
{
	/**
	 * First function being called via AJAX.
	 *
	 * @return InitResource
	 *
	 * @throws VersionControlException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws InvalidOrderDirectionException
	 *
	 * @codeCoverageIgnore Legacy stuff
	 */
	public function init(): InitResource
	{
		try {
			return InitResource::make();
		} catch (ModelDBException $e) {
			$this->logout();
			throw $e;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

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
		if (AdminAuthentication::loginAsAdmin($request->username(), $request->password(), $request->ip())) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		if (Auth::attempt(['username' => $request->username(), 'password' => $request->password()])) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' -- User (' . $request->username() . ') has logged in from ' . $request->ip());

			return;
		}

		// @codeCoverageIgnoreStart
		// TODO: We could avoid this separate log entry and let the exception handler to all the logging, if we would add "context" (see Laravel docs) to those exceptions which need it.
		Log::channel('login')->error(__METHOD__ . ':' . __LINE__ . ' -- User (' . $request->username() . ') has tried to log in from ' . $request->ip());

		throw new UnauthenticatedException('Unknown user or invalid password');
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Unsets the session values.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user();
		Log::channel('login')->info(__METHOD__ . ':' . __LINE__ . ' -- User (' . $user->username . ') has logged out.');
		Auth::logout();
		Session::flush();
	}
}
