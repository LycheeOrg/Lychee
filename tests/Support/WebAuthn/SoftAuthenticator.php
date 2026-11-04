<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Support\WebAuthn;

use CBOR\ByteStringObject;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\UnsignedIntegerObject;
use ParagonIE\ConstantTime\Base64UrlSafe;
use function Safe\json_encode;
use function Safe\openssl_pkey_get_details;
use function Safe\openssl_pkey_new;
use function Safe\openssl_sign;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\TrustPath\EmptyTrustPath;

/**
 * Software authenticator for tests (Feature 107, FX-107-02).
 *
 * Holds an ES256 (P-256) key pair and produces login payloads in the form the
 * front-end helper sends them (FR-107-10): unpadded base64url everywhere.
 */
final class SoftAuthenticator
{
	public const FLAG_USER_PRESENT = 0x01;
	public const FLAG_USER_VERIFIED = 0x04;

	public readonly string $credential_id;
	public readonly string $user_handle;
	private \OpenSSLAsymmetricKey $private_key;

	/**
	 * @param string|null $credential_id raw credential id bytes, random when null
	 * @param string|null $user_handle   raw user handle bytes, random when null
	 */
	public function __construct(?string $credential_id = null, ?string $user_handle = null)
	{
		$this->private_key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
		$this->credential_id = $credential_id ?? random_bytes(32);
		$this->user_handle = $user_handle ?? random_bytes(32);
	}

	public function credentialId(): string
	{
		return Base64UrlSafe::encodeUnpadded($this->credential_id);
	}

	public function userHandle(): string
	{
		return Base64UrlSafe::encodeUnpadded($this->user_handle);
	}

	public function publicKeyPem(): string
	{
		/** @var array{key:string} $details */
		$details = openssl_pkey_get_details($this->private_key);

		return $details['key'];
	}

	/**
	 * The public key as a COSE_Key (EC2, ES256, P-256).
	 */
	public function cosePublicKey(): string
	{
		/** @var array{ec:array{x:string,y:string}} $details */
		$details = openssl_pkey_get_details($this->private_key);

		return (string) MapObject::create()
			->add(UnsignedIntegerObject::create(1), UnsignedIntegerObject::create(2))
			->add(UnsignedIntegerObject::create(3), NegativeIntegerObject::create(-7))
			->add(NegativeIntegerObject::create(-1), UnsignedIntegerObject::create(1))
			->add(NegativeIntegerObject::create(-2), ByteStringObject::create(str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)))
			->add(NegativeIntegerObject::create(-3), ByteStringObject::create(str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT)));
	}

	public function credentialRecord(int $counter = 0): CredentialRecord
	{
		return CredentialRecord::create(
			publicKeyCredentialId: $this->credential_id,
			type: 'public-key',
			transports: [],
			attestationType: 'none',
			trustPath: EmptyTrustPath::create(),
			aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
			credentialPublicKey: $this->cosePublicKey(),
			userHandle: $this->user_handle,
			counter: $counter,
		);
	}

	/**
	 * Signed `webauthn.get` response, as posted to `WebAuthn::login`.
	 *
	 * @param string      $challenge   raw challenge bytes from the request options
	 * @param int         $counter     signature counter reported by the authenticator
	 * @param bool        $with_handle whether the response carries a user handle (discoverable credential)
	 * @param string|null $user_handle handle to send instead of the own one
	 *
	 * @return array{id:string,rawId:string,type:string,response:array{authenticatorData:string,clientDataJSON:string,signature:string,userHandle:string|null}}
	 */
	public function assertion(
		string $challenge,
		int $counter = 1,
		bool $with_handle = false,
		?string $user_handle = null,
		string $origin = 'https://localhost',
		string $rp_id = 'localhost',
		int $flags = self::FLAG_USER_PRESENT,
	): array {
		$authenticator_data = hash('sha256', $rp_id, true) . chr($flags) . pack('N', $counter);
		$client_data_json = json_encode([
			'type' => 'webauthn.get',
			'challenge' => Base64UrlSafe::encodeUnpadded($challenge),
			'origin' => $origin,
			'crossOrigin' => false,
		]);
		openssl_sign($authenticator_data . hash('sha256', $client_data_json, true), $signature, $this->private_key, OPENSSL_ALGO_SHA256);
		/** @var string $signature */
		$handle = $with_handle ? ($user_handle ?? $this->user_handle) : null;

		return [
			'id' => $this->credentialId(),
			'rawId' => $this->credentialId(),
			'type' => 'public-key',
			'response' => [
				'authenticatorData' => Base64UrlSafe::encodeUnpadded($authenticator_data),
				'clientDataJSON' => Base64UrlSafe::encodeUnpadded($client_data_json),
				'signature' => Base64UrlSafe::encodeUnpadded($signature),
				'userHandle' => $handle === null ? null : Base64UrlSafe::encodeUnpadded($handle),
			],
		];
	}
}
