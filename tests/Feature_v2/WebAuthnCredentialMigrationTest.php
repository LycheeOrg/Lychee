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

namespace Tests\Feature_v2;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 107: FR-107-09, S-107-21, S-107-22 (ADR-107-02).
 */
class WebAuthnCredentialMigrationTest extends BaseApiWithDataTest
{
	/** Public key of the recorded login fixture in WebAuthTest, as Laragear stored it. */
	public const FIXTURE_PEM = "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEF25MWDQwaeFqZJ2Gy/7HEdZkWaW+\nQCbWjLiplbklmqIq6MSSRhLqJLoegR5PqG2JOqhSLcQDAmf/tzdAvO5MmQ==\n-----END PUBLIC KEY-----\n";

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebAuthnCredentials();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebAuthnCredentials();
		parent::tearDown();
	}

	public function testLaragearRowIsConvertedToCose(): void
	{
		$this->insertLaragearRow('converted-row', Crypt::encryptString(self::FIXTURE_PEM));

		$this->migration()->up();

		$stored = Crypt::decryptString($this->storedKey('converted-row'));
		self::assertSame($this->migration()->pemToCose(self::FIXTURE_PEM), Base64UrlSafe::decodeNoPadding($stored));
	}

	public function testSecondRunChangesNothing(): void
	{
		$this->insertLaragearRow('converted-twice', Crypt::encryptString(self::FIXTURE_PEM));
		$this->migration()->up();
		$after_first_run = $this->storedKey('converted-twice');

		$this->migration()->up();

		self::assertSame($after_first_run, $this->storedKey('converted-twice'));
	}

	public function testRowUnderAnotherKeyIsLeftAndReported(): void
	{
		$other_key = new Encrypter(random_bytes(32), 'aes-256-cbc');
		$foreign = $other_key->encryptString(self::FIXTURE_PEM);
		$this->insertLaragearRow('foreign-row', $foreign);
		$this->insertLaragearRow('fine-row', Crypt::encryptString(self::FIXTURE_PEM));

		Log::shouldReceive('warning')->once()->withArgs(
			fn (string $message): bool => str_contains($message, '1 WebAuthn credential') &&
				str_contains($message, 'foreign-row') &&
				!str_contains($message, 'fine-row') &&
				!str_contains($message, 'PUBLIC KEY')
		);

		$this->migration()->up();

		self::assertSame($foreign, $this->storedKey('foreign-row'));
		self::assertNotSame(self::FIXTURE_PEM, Crypt::decryptString($this->storedKey('fine-row')));
	}

	public function testDownRestoresThePem(): void
	{
		$this->insertLaragearRow('rolled-back', Crypt::encryptString(self::FIXTURE_PEM));
		$this->migration()->up();

		$this->migration()->down();

		self::assertSame(self::FIXTURE_PEM, Crypt::decryptString($this->storedKey('rolled-back')));
	}

	private function migration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_11_04_000002_convert_webauthn_public_keys.php');

		return $migration;
	}

	private function insertLaragearRow(string $id, string $encrypted_public_key): void
	{
		DB::table('webauthn_credentials')->insert([
			'id' => $id,
			'authenticatable_type' => \App\Models\User::class,
			'authenticatable_id' => $this->admin->id,
			'user_handle' => '27117450ff81461d80331fb79c655f39',
			'alias' => null,
			'counter' => 0,
			'rp_id' => 'localhost',
			'origin' => 'https://localhost',
			'transports' => null,
			'aaguid' => '00000000-0000-0000-0000-000000000000',
			'public_key' => $encrypted_public_key,
			'attestation_format' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		]);
	}

	private function storedKey(string $id): string
	{
		return strval(DB::table('webauthn_credentials')->where('id', '=', $id)->value('public_key'));
	}
}
