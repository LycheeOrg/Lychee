<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Migrations;

use CBOR\Decoder;
use CBOR\MapObject;
use CBOR\StringStream;
use Cose\Algorithm\Signature\ECDSA\ECSignature;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\EdDSA\EdDSA;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Key\Key;
use Illuminate\Database\Migrations\Migration;
use ParagonIE\ConstantTime\Base64UrlSafe;
use function Safe\openssl_pkey_get_details;
use function Safe\openssl_pkey_get_public;
use function Safe\openssl_pkey_new;
use function Safe\openssl_sign;
use Tests\AbstractTestCase;

/**
 * Feature 107: FR-107-09, S-107-20, S-107-23 (ADR-107-02).
 *
 * The conversion lives in the migration files, which are required here.
 */
class WebAuthnKeyConversionTest extends AbstractTestCase
{
	private const MESSAGE = 'authenticator data and client data hash';

	public function testEc2KeyConvertsAndVerifies(): void
	{
		$private = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
		$pem = $this->publicPem($private);

		$cose = $this->keys()->pemToCose($pem);

		$map = $this->decode($cose);
		self::assertSame(2, intval($map[1]), 'kty EC2');
		self::assertSame(-7, intval($map[3]), 'alg ES256');
		self::assertSame(1, intval($map[-1]), 'crv P-256');
		self::assertSame(32, strlen($map[-2]));
		self::assertSame(32, strlen($map[-3]));

		openssl_sign(self::MESSAGE, $der_signature, $private, OPENSSL_ALGO_SHA256);
		self::assertTrue(ES256::create()->verify(self::MESSAGE, Key::createFromData($map), ECSignature::fromAsn1($der_signature, 64)));
	}

	public function testRsaKeyConvertsAndVerifies(): void
	{
		$private = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
		$pem = $this->publicPem($private);

		$map = $this->decode($this->keys()->pemToCose($pem));
		self::assertSame(3, intval($map[1]), 'kty RSA');
		self::assertSame(-257, intval($map[3]), 'alg RS256');

		openssl_sign(self::MESSAGE, $signature, $private, OPENSSL_ALGO_SHA256);
		self::assertTrue(RS256::create()->verify(self::MESSAGE, Key::createFromData($map), $signature));
	}

	public function testEd25519KeyConvertsAndVerifies(): void
	{
		$key_pair = sodium_crypto_sign_keypair();
		$pem = $this->ed25519Pem(sodium_crypto_sign_publickey($key_pair));

		$map = $this->decode($this->keys()->pemToCose($pem));
		self::assertSame(1, intval($map[1]), 'kty OKP');
		self::assertSame(-8, intval($map[3]), 'alg EdDSA');
		self::assertSame(6, intval($map[-1]), 'crv Ed25519');

		$signature = sodium_crypto_sign_detached(self::MESSAGE, sodium_crypto_sign_secretkey($key_pair));
		self::assertTrue((new EdDSA())->verify(self::MESSAGE, Key::createFromData($map), $signature));
	}

	public function testCoseConvertsBackToTheSamePem(): void
	{
		$ec = $this->publicPem(openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']));
		$rsa = $this->publicPem(openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]));
		$ed25519 = $this->ed25519Pem(sodium_crypto_sign_publickey(sodium_crypto_sign_keypair()));

		foreach ([$ec, $rsa, $ed25519] as $pem) {
			self::assertSame($pem, $this->keys()->coseToPem($this->keys()->pemToCose($pem)));
		}
	}

	public function testOtherCurvesAreRejected(): void
	{
		$pem = $this->publicPem(openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'secp384r1']));

		$this->expectException(\UnexpectedValueException::class);
		$this->keys()->pemToCose($pem);
	}

	public function testGarbageIsRejected(): void
	{
		$this->expectException(\Throwable::class);
		$this->keys()->pemToCose("-----BEGIN PUBLIC KEY-----\nbm90IGEga2V5\n-----END PUBLIC KEY-----\n");
	}

	public function testIsPem(): void
	{
		self::assertTrue($this->keys()->isPem("-----BEGIN PUBLIC KEY-----\nAAAA\n-----END PUBLIC KEY-----\n"));
		self::assertFalse($this->keys()->isPem(Base64UrlSafe::encodeUnpadded('cose bytes')));
	}

	/**
	 * S-107-23: Laragear's `user_id` in the PostgreSQL (dashed, any case) and
	 * MySQL/SQLite (plain hex) forms.
	 */
	public function testUserHandleFromLaragearUuid(): void
	{
		$handles = $this->handles();

		self::assertSame('dfe5446bfd664e599b6140fafd426489', $handles->toUserHandle('DFE5446B-FD66-4E59-9B61-40FAFD426489'));
		self::assertSame('dfe5446bfd664e599b6140fafd426489', $handles->toUserHandle('dfe5446bfd664e599b6140fafd426489'));
	}

	/**
	 * The authenticator holds `base64url_decode(hex)` (the vendored helper decoded
	 * Laragear's `user.id`), so the stored base64url value is the hex string itself.
	 */
	public function testUserHandleIsTheBase64UrlOfTheBytesAuthenticatorsHold(): void
	{
		$hex = 'dfe5446bfd664e599b6140fafd426489';
		$bytes_on_authenticator = Base64UrlSafe::decodeNoPadding($hex);

		self::assertSame(24, strlen($bytes_on_authenticator));
		self::assertSame($hex, Base64UrlSafe::encodeUnpadded($bytes_on_authenticator));
	}

	public function testUserIdFromUserHandle(): void
	{
		$handles = $this->handles();

		self::assertSame('dfe5446bfd664e599b6140fafd426489', $handles->toUserId('dfe5446bfd664e599b6140fafd426489'));
		self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $handles->toUserId(Base64UrlSafe::encodeUnpadded(random_bytes(32))));
	}

	private function keys(): Migration
	{
		return require base_path('database/migrations/2026_11_04_000002_convert_webauthn_public_keys.php');
	}

	private function handles(): Migration
	{
		return require base_path('database/migrations/2026_11_04_000001_webauthn_credentials_user_handle.php');
	}

	private function publicPem(\OpenSSLAsymmetricKey $private): string
	{
		/** @var array{key:string} $details */
		$details = openssl_pkey_get_details(openssl_pkey_get_public(openssl_pkey_get_details($private)['key']));

		return $details['key'];
	}

	/**
	 * PEM as Laragear wrote it for an OKP key: SubjectPublicKeyInfo with OID 1.3.101.112.
	 */
	private function ed25519Pem(string $public_key): string
	{
		$der = hex2bin('302a300506032b6570032100') . $public_key;

		return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
	}

	/**
	 * @return array<int,int|string>
	 */
	private function decode(string $cose): array
	{
		$object = Decoder::create()->decode(StringStream::create($cose));
		self::assertInstanceOf(MapObject::class, $object);

		return $object->normalize();
	}
}
