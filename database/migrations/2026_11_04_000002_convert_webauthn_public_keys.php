<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use CBOR\ByteStringObject;
use CBOR\Decoder;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\StringStream;
use CBOR\UnsignedIntegerObject;
use Cose\Key\Ec2Key;
use Cose\Key\Key;
use Cose\Key\OkpKey;
use Cose\Key\RsaKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ParagonIE\ConstantTime\Base64UrlSafe;
use function Safe\base64_decode;
use function Safe\hex2bin;
use function Safe\openssl_pkey_get_details;
use function Safe\openssl_pkey_get_public;
use function Safe\preg_replace;

/**
 * Feature 107 (FR-107-09, ADR-107-02): public keys of `webauthn_credentials`
 * from Laragear's PEM to the COSE key `web-auth/webauthn-lib` verifies with.
 *
 * Laragear only accepted ES256 (P-256), RS256 and EdDSA (Ed25519) keys, so the
 * key type gives the algorithm. The column stays encrypted with `APP_KEY` and
 * holds the base64url of the COSE key. Rows that cannot be decrypted (rotated
 * `APP_KEY`) or parsed are left as they are and listed in one warning; their
 * owners register the key again. Converted rows are skipped, so the migration
 * can run again.
 *
 * The conversion lives here on purpose: later changes to app classes cannot
 * break it.
 */
return new class() extends Migration {
	private const TABLE = 'webauthn_credentials';
	private const PEM_HEADER = '-----BEGIN PUBLIC KEY-----';
	/** DER of an Ed25519 SubjectPublicKeyInfo (RFC 8410) up to the 32-byte key. */
	private const ED25519_SPKI_PREFIX = '302a300506032b6570032100';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->convertAll(
			fn (string $key): bool => $this->isPem($key),
			fn (string $pem): string => Base64UrlSafe::encodeUnpadded($this->pemToCose($pem)),
		);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->convertAll(
			fn (string $key): bool => !$this->isPem($key),
			fn (string $cose): string => $this->coseToPem(Base64UrlSafe::decodeNoPadding($cose)),
		);
	}

	public function isPem(string $key): bool
	{
		return str_starts_with($key, self::PEM_HEADER);
	}

	/**
	 * Laragear PEM (SubjectPublicKeyInfo) to a COSE_Key.
	 *
	 * @throws UnexpectedValueException for key types Laragear never accepted
	 */
	public function pemToCose(string $pem): string
	{
		$der = base64_decode(preg_replace('/-----[A-Z ]+-----|\s/', '', $pem), true);
		$ed25519_prefix = hex2bin(self::ED25519_SPKI_PREFIX);
		if (strlen($der) === 44 && str_starts_with($der, $ed25519_prefix)) {
			return $this->okp(substr($der, strlen($ed25519_prefix)));
		}

		/** @var array{type:int,ec?:array{curve_name:string,x:string,y:string},rsa?:array{n:string,e:string}} $details */
		$details = openssl_pkey_get_details(openssl_pkey_get_public($pem));

		return match ($details['type']) {
			OPENSSL_KEYTYPE_EC => $this->ec2($details['ec'] ?? []),
			OPENSSL_KEYTYPE_RSA => $this->rsa($details['rsa'] ?? []),
			default => throw new UnexpectedValueException('Unsupported public key type.'),
		};
	}

	/**
	 * COSE_Key back to PEM, for `down()`, ending with a newline as Laragear wrote it.
	 */
	public function coseToPem(string $cose): string
	{
		$object = Decoder::create()->decode(StringStream::create($cose));
		if (!$object instanceof MapObject) {
			throw new UnexpectedValueException('Not a COSE key.');
		}

		$key = Key::createFromData($object->normalize());
		$pem = match (true) {
			$key instanceof Ec2Key, $key instanceof OkpKey => $key->asPEM(),
			$key instanceof RsaKey => $key->asPem(),
			default => throw new UnexpectedValueException('Unsupported COSE key type.'),
		};

		return rtrim($pem) . "\n";
	}

	/**
	 * Re-encrypt every key `$is_source` accepts with `$transform` applied, reading the rows in batches.
	 *
	 * @param Closure(string):bool   $is_source
	 * @param Closure(string):string $transform
	 */
	private function convertAll(Closure $is_source, Closure $transform): void
	{
		$failed = [];
		foreach (DB::table(self::TABLE)->select(['id', 'public_key'])->lazyById() as $row) {
			try {
				$key = Crypt::decryptString(strval($row->public_key));
				$converted = $is_source($key) ? Crypt::encryptString($transform($key)) : null;
			} catch (Throwable) {
				$failed[] = strval($row->id);
				continue;
			}

			if ($converted !== null) {
				DB::table(self::TABLE)->where('id', '=', $row->id)->update(['public_key' => $converted]);
			}
		}

		if (count($failed) > 0) {
			Log::warning(sprintf(
				'%d WebAuthn credential(s) could not be converted (was APP_KEY changed?) and must be registered again: %s',
				count($failed),
				implode(', ', $failed)
			));
		}
	}

	/**
	 * @param array{curve_name?:string,x?:string,y?:string} $ec
	 */
	private function ec2(array $ec): string
	{
		if (($ec['curve_name'] ?? '') !== 'prime256v1') {
			throw new UnexpectedValueException('Unsupported curve.');
		}

		return $this->coseKey([
			[1, UnsignedIntegerObject::create(2)],
			[3, NegativeIntegerObject::create(-7)],
			[-1, UnsignedIntegerObject::create(1)],
			[-2, ByteStringObject::create(str_pad($ec['x'] ?? '', 32, "\0", STR_PAD_LEFT))],
			[-3, ByteStringObject::create(str_pad($ec['y'] ?? '', 32, "\0", STR_PAD_LEFT))],
		]);
	}

	/**
	 * @param array{n?:string,e?:string} $rsa
	 */
	private function rsa(array $rsa): string
	{
		return $this->coseKey([
			[1, UnsignedIntegerObject::create(3)],
			[3, NegativeIntegerObject::create(-257)],
			[-1, ByteStringObject::create($rsa['n'] ?? '')],
			[-2, ByteStringObject::create($rsa['e'] ?? '')],
		]);
	}

	private function okp(string $x): string
	{
		return $this->coseKey([
			[1, UnsignedIntegerObject::create(1)],
			[3, NegativeIntegerObject::create(-8)],
			[-1, UnsignedIntegerObject::create(6)],
			[-2, ByteStringObject::create($x)],
		]);
	}

	/**
	 * @param list<array{int,CBOR\CBORObject}> $entries COSE label and value, in this order
	 */
	private function coseKey(array $entries): string
	{
		$map = MapObject::create();
		foreach ($entries as [$label, $value]) {
			$map->add($label < 0 ? NegativeIntegerObject::create($label) : UnsignedIntegerObject::create($label), $value);
		}

		return (string) $map;
	}
};
