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
use Illuminate\Support\Collection;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Creation options for a new security key of the signed-in user (Feature 107, FR-107-01/02).
 *
 * Policy kept from Laragear's `fastRegistration()` (Q-107-03 A): user
 * verification discouraged, discoverable credential preferred, no attestation.
 */
final class GenerateRegistrationOptions
{
	/** Size of a new user handle, the WebAuthn maximum is 64. */
	private const USER_HANDLE_BYTES = 32;

	public function __construct(
		private readonly WebAuthnFactory $factory,
		private readonly CeremonyStore $store,
	) {
	}

	/**
	 * @return array<array-key,mixed> the options as the browser receives them
	 */
	public function do(User $user): array
	{
		/** @var Collection<int,WebAuthnCredential> $credentials */
		$credentials = $user->webAuthnCredentials()->get();

		$options = PublicKeyCredentialCreationOptions::create(
			rp: PublicKeyCredentialRpEntity::create($this->factory->relyingPartyName(), $this->factory->relyingPartyId()),
			user: PublicKeyCredentialUserEntity::create($user->webAuthnUserName(), $this->userHandle($credentials), $user->name),
			challenge: random_bytes($this->factory->challengeBytes()),
			pubKeyCredParams: array_map(fn (int $algorithm) => PublicKeyCredentialParameters::createPk($algorithm), $this->factory->algorithms()),
			authenticatorSelection: AuthenticatorSelectionCriteria::create(
				userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED,
				residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
			),
			attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
			excludeCredentials: $credentials->map(fn (WebAuthnCredential $credential) => $credential->toDescriptor())->values()->all(),
			timeout: $this->factory->timeoutMilliseconds(),
		);

		$this->store->putCreationOptions($options, $user->id);

		return $this->factory->toBrowserArray($options);
	}

	/**
	 * The handle of the user's existing keys, so all of them share it (FR-107-02).
	 *
	 * @param Collection<int,WebAuthnCredential> $credentials
	 */
	private function userHandle(#[\SensitiveParameter] Collection $credentials): string
	{
		$existing = $credentials->first()?->user_handle;

		return $existing !== null ? Base64UrlSafe::decodeNoPadding($existing) : random_bytes(self::USER_HANDLE_BYTES);
	}
}
