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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 107: FR-107-09, S-107-21, S-107-22, S-107-23 (ADR-107-02).
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
		try {
			$this->tearDownRequiresEmptyWebAuthnCredentials();
			// SQLite and PostgreSQL roll schema changes back with the test transaction, MySQL and
			// MariaDB commit them at once: put the table back when a test stopped after `down()`.
			if (Schema::hasColumn('webauthn_credentials', 'user_id')) {
				$this->handleMigration()->up();
			}
		} finally {
			parent::tearDown();
		}
	}

	/**
	 * S-107-23: `up()` on the table as Laragear left it.
	 */
	public function testUserIdBecomesUserHandle(): void
	{
		$this->handleMigration()->down();
		$this->insertLegacyRow('dashed-row', '27117450-FF81-461D-8033-1FB79C655F39');
		$this->insertLegacyRow('plain-row', 'dfe5446bfd664e599b6140fafd426489');

		$this->handleMigration()->up();

		self::assertFalse(Schema::hasColumn('webauthn_credentials', 'user_id'));
		self::assertTrue(Schema::hasColumns('webauthn_credentials', ['user_handle', 'backup_eligible', 'backup_status', 'uv_initialized']));
		self::assertSame('27117450ff81461d80331fb79c655f39', $this->storedValue('dashed-row', 'user_handle'));
		self::assertSame('dfe5446bfd664e599b6140fafd426489', $this->storedValue('plain-row', 'user_handle'));
	}

	/**
	 * S-107-23: `down()` gives Laragear its `user_id` back, `up()` then restores the handle.
	 */
	public function testUserHandleBecomesUserIdOnRollback(): void
	{
		$this->insertLaragearRow('hex-handle', Crypt::encryptString(self::FIXTURE_PEM));
		$this->insertLaragearRow('random-handle', Crypt::encryptString(self::FIXTURE_PEM), Base64UrlSafe::encodeUnpadded(random_bytes(32)));

		$this->handleMigration()->down();

		self::assertFalse(Schema::hasColumn('webauthn_credentials', 'user_handle'));
		self::assertSame('27117450ff81461d80331fb79c655f39', $this->uuidHex($this->storedValue('hex-handle', 'user_id')));
		self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $this->uuidHex($this->storedValue('random-handle', 'user_id')));

		$this->handleMigration()->up();

		self::assertSame('27117450ff81461d80331fb79c655f39', $this->storedValue('hex-handle', 'user_handle'));
	}

	/**
	 * S-107-23: a run cut short after its first column (MySQL commits each DDL statement) completes when started again.
	 */
	public function testInterruptedUserHandleMigrationCompletes(): void
	{
		$this->handleMigration()->down();
		$this->insertLegacyRow('resumed-row', 'dfe5446b-fd66-4e59-9b61-40fafd426489');
		Schema::table('webauthn_credentials', function (Blueprint $table) {
			$table->string('user_handle', 128)->nullable()->after('authenticatable_id');
		});

		$this->handleMigration()->up();
		$this->handleMigration()->up();

		self::assertFalse(Schema::hasColumn('webauthn_credentials', 'user_id'));
		self::assertSame('dfe5446bfd664e599b6140fafd426489', $this->storedValue('resumed-row', 'user_handle'));
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

	private function handleMigration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_11_04_000001_webauthn_credentials_user_handle.php');

		return $migration;
	}

	private function migration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_11_04_000002_convert_webauthn_public_keys.php');

		return $migration;
	}

	/**
	 * A row of the table before `2026_11_04_000001`: Laragear's `user_id`, no `user_handle`.
	 */
	private function insertLegacyRow(string $id, string $user_id): void
	{
		DB::table('webauthn_credentials')->insert([
			'id' => $id,
			'authenticatable_type' => \App\Models\User::class,
			'authenticatable_id' => $this->admin->id,
			'user_id' => $user_id,
			'counter' => 0,
			'rp_id' => 'localhost',
			'origin' => 'https://localhost',
			'public_key' => Crypt::encryptString(self::FIXTURE_PEM),
			'attestation_format' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		]);
	}

	private function insertLaragearRow(string $id, string $encrypted_public_key, string $user_handle = '27117450ff81461d80331fb79c655f39'): void
	{
		DB::table('webauthn_credentials')->insert([
			'id' => $id,
			'authenticatable_type' => \App\Models\User::class,
			'authenticatable_id' => $this->admin->id,
			'user_handle' => $user_handle,
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
		return $this->storedValue($id, 'public_key');
	}

	private function storedValue(string $id, string $column): string
	{
		return strval(DB::table('webauthn_credentials')->where('id', '=', $id)->value($column));
	}

	/**
	 * PostgreSQL returns its native `uuid` type with dashes, SQLite and MySQL/MariaDB (`char(36)`) as written.
	 */
	private function uuidHex(string $uuid): string
	{
		return strtolower(str_replace('-', '', $uuid));
	}
}
