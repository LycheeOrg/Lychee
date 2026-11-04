<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\WebAuthn;

use App\Actions\WebAuthn\GenerateLoginOptions;
use App\Actions\WebAuthn\VerifyLogin;
use App\Exceptions\WebAuthnDisabledExecption;
use App\Exceptions\WebAuthnVerificationException;
use App\Http\Requests\WebAuthn\LoginCredentialRequest;
use App\Http\Requests\WebAuthn\LoginOptionsRequest;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class WebAuthnLoginController extends Controller
{
	/**
	 * Returns the challenge to assertion (FR-107-04).
	 *
	 * @return array<array-key,mixed>
	 *
	 * @throws WebAuthnDisabledExecption
	 */
	public function options(LoginOptionsRequest $request, GenerateLoginOptions $generate): array
	{
		$this->checkEnabled();

		return $generate->do($this->findUser($request));
	}

	/**
	 * Log the user in (FR-107-05).
	 *
	 * The key is looked up by its id first, so the owner is known before the
	 * assertion is checked: keys without a user handle (e.g. YubiKey 4) work.
	 *
	 * @throws WebAuthnDisabledExecption
	 * @throws WebAuthnVerificationException
	 */
	public function login(LoginCredentialRequest $request, VerifyLogin $verify): void
	{
		$this->checkEnabled();

		Auth::login($verify->do($request->publicKeyCredential()));
	}

	/**
	 * The user whose keys the options allow, if the request names one.
	 */
	private function findUser(LoginOptionsRequest $request): ?User
	{
		if ($request->userId() !== null) {
			return User::query()->find($request->userId());
		}

		$username = $request->username();

		return $username === null ? null : User::query()->where('username', '=', $this->resolveIdentifier($username))->first();
	}

	/**
	 * Translate a submitted identifier into the username the credential list is narrowed by.
	 *
	 * `email_login_enabled` lets the login form accept an email address
	 * (Feature 091), so the same identifier has to find its credentials here.
	 * The username stays authoritative: the email lookup only runs when no
	 * account carries that username, which mirrors the attempt order of
	 * `AuthController::attemptLocalLogin()`.
	 *
	 * @param string $identifier submitted username or email address
	 *
	 * @return string the username to narrow the credential list by
	 */
	private function resolveIdentifier(string $identifier): string
	{
		if (!resolve(ConfigManager::class)->getValueAsBool('email_login_enabled')) {
			return $identifier;
		}

		if (User::query()->where('username', '=', $identifier)->exists()) {
			return $identifier;
		}

		return User::query()->where('email', '=', $identifier)->first()?->username ?? $identifier;
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
