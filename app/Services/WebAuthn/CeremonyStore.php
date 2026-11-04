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
 * after `webauthn.challenge.timeout` seconds.
 */
final class CeremonyStore
{
	private const CREATION = 'attestation';
	private const REQUEST = 'assertion';

	public function __construct(
		private readonly WebAuthnFactory $factory,
	) {
	}

	public function putCreationOptions(PublicKeyCredentialCreationOptions $options): void
	{
		$this->put(self::CREATION, $options);
	}

	public function putRequestOptions(PublicKeyCredentialRequestOptions $options): void
	{
		$this->put(self::REQUEST, $options);
	}

	/**
	 * @throws WebAuthnVerificationException when nothing is pending or it expired
	 */
	public function pullCreationOptions(): PublicKeyCredentialCreationOptions
	{
		return $this->factory->fromJson($this->pull(self::CREATION), PublicKeyCredentialCreationOptions::class);
	}

	/**
	 * @throws WebAuthnVerificationException when nothing is pending or it expired
	 */
	public function pullRequestOptions(): PublicKeyCredentialRequestOptions
	{
		return $this->factory->fromJson($this->pull(self::REQUEST), PublicKeyCredentialRequestOptions::class);
	}

	private function put(string $slot, PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions $options): void
	{
		Session::put($this->key($slot), [
			'options' => $this->factory->toJson($options),
			'expires_at' => now()->getTimestamp() + intval(config('webauthn.challenge.timeout', 60)),
		]);
	}

	/**
	 * @return string the serialised options
	 *
	 * @throws WebAuthnVerificationException
	 */
	private function pull(string $slot): string
	{
		$entry = Session::pull($this->key($slot));
		if (!is_array($entry) || !is_string($entry['options'] ?? null) || !is_int($entry['expires_at'] ?? null)) {
			throw new WebAuthnVerificationException('No pending WebAuthn challenge. Please try again.');
		}

		if (now()->getTimestamp() > $entry['expires_at']) {
			throw new WebAuthnVerificationException('The WebAuthn challenge expired. Please try again.');
		}

		return $entry['options'];
	}

	private function key(string $slot): string
	{
		return strval(config('webauthn.challenge.key', '_webauthn')) . '.' . $slot;
	}
}
