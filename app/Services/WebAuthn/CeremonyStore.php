<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\WebAuthn;

use App\Exceptions\WebAuthnVerificationException;
use Illuminate\Support\Facades\Session;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Keeps the options of a pending WebAuthn ceremony in the session (Feature 107).
 *
 * Registration and login have their own slot under `webauthn.challenge.key`.
 * Each entry is single use: pulling it removes it, whether it is still valid
 * or not, so a challenge can never be answered twice (NFR-107-04). It expires
 * after `webauthn.challenge.timeout` seconds, to the microsecond. Registration
 * options belong to the user who asked for them (FR-107-03).
 */
final class CeremonyStore
{
	private const CREATION = 'attestation';
	private const REQUEST = 'assertion';

	public function __construct(
		private readonly WebAuthnFactory $factory,
	) {
	}

	/**
	 * @param int $user_id the user who asks to register a key
	 */
	public function putCreationOptions(PublicKeyCredentialCreationOptions $options, int $user_id): void
	{
		$this->put(self::CREATION, $options, $user_id);
	}

	public function putRequestOptions(PublicKeyCredentialRequestOptions $options): void
	{
		$this->put(self::REQUEST, $options, null);
	}

	/**
	 * @param int $user_id the signed-in user completing the registration
	 *
	 * @throws WebAuthnVerificationException when nothing is pending for this user or it expired
	 */
	public function pullCreationOptions(int $user_id): PublicKeyCredentialCreationOptions
	{
		return $this->factory->fromJson($this->pull(self::CREATION, $user_id), PublicKeyCredentialCreationOptions::class);
	}

	/**
	 * @throws WebAuthnVerificationException when nothing is pending or it expired
	 */
	public function pullRequestOptions(): PublicKeyCredentialRequestOptions
	{
		return $this->factory->fromJson($this->pull(self::REQUEST, null), PublicKeyCredentialRequestOptions::class);
	}

	private function put(string $slot, PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions $options, ?int $user_id): void
	{
		Session::put($this->key($slot), [
			'options' => $this->factory->toJson($options),
			'user_id' => $user_id,
			'expires_at' => $this->nowInMicroseconds() + intval(config('webauthn.challenge.timeout', 60)) * 1_000_000,
		]);
	}

	/**
	 * @return string the serialised options
	 *
	 * @throws WebAuthnVerificationException
	 */
	private function pull(string $slot, ?int $user_id): string
	{
		$entry = Session::pull($this->key($slot));
		if (!is_array($entry) || !is_string($entry['options'] ?? null) || !is_int($entry['expires_at'] ?? null) || ($entry['user_id'] ?? null) !== $user_id) {
			throw new WebAuthnVerificationException('No pending WebAuthn challenge. Please try again.');
		}

		if ($this->nowInMicroseconds() > $entry['expires_at']) {
			throw new WebAuthnVerificationException('The WebAuthn challenge expired. Please try again.');
		}

		return $entry['options'];
	}

	/**
	 * Carbon's clock, so `travel()` moves it in tests.
	 */
	private function nowInMicroseconds(): int
	{
		return intval(now()->getPreciseTimestamp(6));
	}

	private function key(string $slot): string
	{
		return strval(config('webauthn.challenge.key', '_webauthn')) . '.' . $slot;
	}
}
