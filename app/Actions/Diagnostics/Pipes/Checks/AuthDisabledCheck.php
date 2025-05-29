<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Schema;

class AuthDisabledCheck implements DiagnosticPipe
{
	public const INFO = 'You need to enable at least one authentication method to be able to use Lychee...';

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('users') || !Schema::hasTable('oauth_credentials') || !Schema::hasTable('webauthn_credentials')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if (AuthServiceProvider::isBasicAuthEnabled()) {
			// If basic auth is enabled, we do not need to check for Oauth or WebAuthn
			// as they are optional and can be used in addition to basic auth.
			return $next($data);
		}
		// From now on, we assume that basic auth is disabled.

		if (!AuthServiceProvider::isWebAuthnEnabled() && !AuthServiceProvider::isOauthEnabled()) {
			$data[] = DiagnosticData::error('All authentication methods are disabled. Really?', self::class, [self::INFO]);

			return $next($data);
		}

		$number_admin_with_oauth = AuthServiceProvider::isOauthEnabled() ? $this->oauthChecks($data) : 0;
		$number_admin_with_webauthn = AuthServiceProvider::isWebAuthnEnabled() ? $this->webauthnCheck($data) : 0;
		if (($number_admin_with_oauth === 0 && AuthServiceProvider::isOauthEnabled()) &&
			($number_admin_with_webauthn === 0 && AuthServiceProvider::isWebAuthnEnabled())
		) {
			$data[] = DiagnosticData::error('Basic auth is disabled and there are no admin user with Oauth or WebAuthn enabled.', self::class, [self::INFO]);
		}

		return $next($data);
	}

	/**
	 * @param DiagnosticData[] &$data
	 *
	 * @return int
	 */
	private function oauthChecks(array &$data): int
	{
		$number_admin_with_oauth = User::query()->has('oauthCredentials')->where('may_administrate', '=', true)->count();
		if (!AuthServiceProvider::isWebAuthnEnabled() && $number_admin_with_oauth === 0) {
			$data[] = DiagnosticData::error('Basic auth and Webauthn are disabled and there are no admin user with Oauth enabled.', self::class, [self::INFO]);
		}

		return $number_admin_with_oauth;
	}

	/**
	 * @param DiagnosticData[] &$data
	 *
	 * @return int
	 */
	private function webauthnCheck(array &$data): int
	{
		$number_admin_with_webauthn = User::query()->has('webAuthnCredentials')->where('may_administrate', '=', true)->count();
		if (!AuthServiceProvider::isOauthEnabled() && $number_admin_with_webauthn === 0) {
			$data[] = DiagnosticData::error('Basic auth is disabled and there are no admin user with WebAuthn enabled.', self::class, [self::INFO]);
		}

		return $number_admin_with_webauthn;
	}
}
