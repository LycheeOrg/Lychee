<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\WebAuthn;

use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Services\WebAuthn\CeremonyStore;
use App\Services\WebAuthn\WebAuthnFactory;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Request options for a login with a security key (Feature 107, FR-107-04).
 *
 * With a user, only their enabled keys are allowed. Without one (or for a user
 * without keys) `allowCredentials` is left out, which lets any discoverable key
 * answer, as Laragear did. User verification is `preferred` (Q-107-03 A).
 */
final class GenerateLoginOptions
{
	public function __construct(
		private readonly WebAuthnFactory $factory,
		private readonly CeremonyStore $store,
	) {
	}

	/**
	 * @return array<array-key,mixed> the options as the browser receives them
	 */
	public function do(?User $user): array
	{
		$credentials = $user?->webAuthnCredentials()->whereNull('disabled_at')->get() ?? collect();

		$options = PublicKeyCredentialRequestOptions::create(
			challenge: random_bytes($this->factory->challengeBytes()),
			rpId: $this->factory->relyingPartyId(),
			allowCredentials: $credentials->map(fn (WebAuthnCredential $credential) => $credential->toDescriptor())->values()->all(),
			userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
			timeout: $this->factory->timeoutMilliseconds(),
		);

		$this->store->putRequestOptions($options);

		$browser_options = $this->factory->toBrowserArray($options);
		if (($browser_options['allowCredentials'] ?? []) === []) {
			unset($browser_options['allowCredentials']);
		}

		return $browser_options;
	}
}
