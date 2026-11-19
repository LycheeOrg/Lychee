<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\WebAuthn;

use App\Exceptions\WebAuthnVerificationException;
use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Services\WebAuthn\CeremonyStore;
use App\Services\WebAuthn\WebAuthnFactory;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialCreationOptions;

/**
 * Verify the browser's attestation against the pending options and store the key
 * (Feature 107, FR-107-03).
 */
final class StoreCredential
{
	public function __construct(
		private readonly WebAuthnFactory $factory,
		private readonly CeremonyStore $store,
	) {
	}

	/**
	 * @param User                $user    the signed-in user
	 * @param array<string,mixed> $payload the browser's `PublicKeyCredential` in JSON form
	 *
	 * @throws WebAuthnVerificationException
	 */
	public function do(User $user, array $payload): WebAuthnCredential
	{
		$options = $this->store->pullCreationOptions($user->id);
		$response = $this->factory->parseCredential($payload)->response;
		if (!$response instanceof AuthenticatorAttestationResponse) {
			throw new WebAuthnVerificationException('Expected a WebAuthn registration response.');
		}

		$record = $this->validate($response, $options);
		$id = Base64UrlSafe::encodeUnpadded($record->publicKeyCredentialId);
		if (WebAuthnCredential::query()->whereKey($id)->exists()) {
			throw new WebAuthnVerificationException('This security key is already registered.');
		}

		$credential = new WebAuthnCredential([
			'id' => $id,
			'user_handle' => Base64UrlSafe::encodeUnpadded($record->userHandle),
			'rp_id' => $options->rp->id ?? $this->factory->relyingPartyId(),
			'origin' => $response->clientDataJSON->origin,
			'transports' => $record->transports,
			'aaguid' => $record->aaguid->toRfc4122(),
			'public_key' => Base64UrlSafe::encodeUnpadded($record->credentialPublicKey),
			'attestation_format' => $response->attestationObject->attStmt->fmt,
		]);
		$credential->fillFromCredentialRecord($record);
		$user->webAuthnCredentials()->save($credential);

		return $credential;
	}

	/**
	 * @throws WebAuthnVerificationException
	 */
	private function validate(AuthenticatorAttestationResponse $response, PublicKeyCredentialCreationOptions $options): CredentialRecord
	{
		try {
			return $this->factory->attestationValidator()->check($response, $options, $options->rp->id ?? $this->factory->relyingPartyId());
		} catch (\Throwable $e) {
			throw new WebAuthnVerificationException('WebAuthn registration failed: ' . $e->getMessage(), $e);
		}
	}
}
