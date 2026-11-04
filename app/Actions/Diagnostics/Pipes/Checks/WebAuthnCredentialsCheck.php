<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\WebAuthnCredential;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Schema;

/**
 * Security keys whose public key cannot be read (Feature 107, FR-107-12).
 *
 * The upgrade migration leaves a key untouched when it cannot decrypt it
 * (rows written under another `APP_KEY`), and such a key fails every login.
 * Only the count is shown: diagnostics can be read by guests.
 */
class WebAuthnCredentialsCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!AuthServiceProvider::isWebAuthnEnabled() || !Schema::hasColumn('webauthn_credentials', 'user_handle')) {
			return $next($data);
		}

		$count = WebAuthnCredential::query()->get()->reject(fn (WebAuthnCredential $credential) => $credential->hasReadablePublicKey())->count();
		if ($count > 0) {
			$data[] = DiagnosticData::warn(sprintf('%d WebAuthn credential(s) could not be read (was APP_KEY changed?).', $count), self::class,
				['Their owners have to register them again.']);
		}

		return $next($data);
	}
}
