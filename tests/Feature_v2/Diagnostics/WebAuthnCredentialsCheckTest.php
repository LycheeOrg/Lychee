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

namespace Tests\Feature_v2\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\WebAuthnCredentialsCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Feature_v2\WebAuthnCredentialMigrationTest;
use Tests\Support\WebAuthn\SoftAuthenticator;

/**
 * Feature 107: FR-107-12, S-107-21.
 */
class WebAuthnCredentialsCheckTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebAuthnCredentials();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebAuthnCredentials();
		config(['features.disable-webauthn' => false]);
		parent::tearDown();
	}

	public function testNoCredentialsAddNothing(): void
	{
		self::assertEmpty($this->runCheck());
	}

	public function testReadableCredentialsAddNothing(): void
	{
		$this->insertRow('readable', Crypt::encryptString(Base64UrlSafe::encodeUnpadded((new SoftAuthenticator())->cosePublicKey())));

		self::assertEmpty($this->runCheck());
	}

	public function testUnreadableCredentialsAreCounted(): void
	{
		$this->insertRow('readable', Crypt::encryptString(Base64UrlSafe::encodeUnpadded((new SoftAuthenticator())->cosePublicKey())));
		$this->insertRow('other-app-key', (new Encrypter(random_bytes(32), 'aes-256-cbc'))->encryptString('anything'));
		$this->insertRow('still-pem', Crypt::encryptString(WebAuthnCredentialMigrationTest::FIXTURE_PEM));

		$result = $this->runCheck();

		self::assertCount(1, $result);
		self::assertEquals(MessageType::WARNING, $result[0]->type);
		self::assertEquals(WebAuthnCredentialsCheck::class, $result[0]->from);
		self::assertEquals('2 WebAuthn credential(s) could not be read (was APP_KEY changed?).', $result[0]->message);
		self::assertEquals(['Their owners have to delete them in their profile, then register them again.'], $result[0]->details);
		self::assertStringNotContainsString('other-app-key', implode(' ', [$result[0]->message, ...$result[0]->details]));
	}

	public function testDisabledWebAuthnAddsNothing(): void
	{
		$this->insertRow('still-pem', Crypt::encryptString(WebAuthnCredentialMigrationTest::FIXTURE_PEM));
		config(['features.disable-webauthn' => true]);

		self::assertEmpty($this->runCheck());
	}

	/**
	 * @return DiagnosticData[]
	 */
	private function runCheck(): array
	{
		$data = [];

		return (new WebAuthnCredentialsCheck())->handle($data, fn (array $data) => $data);
	}

	private function insertRow(string $id, string $public_key): void
	{
		DB::table('webauthn_credentials')->insert([
			'id' => $id,
			'authenticatable_type' => User::class,
			'authenticatable_id' => $this->admin->id,
			'user_handle' => Base64UrlSafe::encodeUnpadded(random_bytes(32)),
			'counter' => 0,
			'rp_id' => 'localhost',
			'origin' => 'https://localhost',
			'public_key' => $public_key,
			'attestation_format' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		]);
	}
}
