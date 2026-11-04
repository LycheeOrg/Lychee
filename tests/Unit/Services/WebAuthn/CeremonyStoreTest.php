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

namespace Tests\Unit\Services\WebAuthn;

use App\Exceptions\WebAuthnVerificationException;
use App\Services\WebAuthn\CeremonyStore;
use Tests\AbstractTestCase;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Feature 107: FR-107-01, FR-107-03, FR-107-04, FR-107-05, S-107-04, S-107-14.
 */
class CeremonyStoreTest extends AbstractTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		config(['webauthn.challenge.timeout' => 60]);
		session()->flush();
	}

	public function tearDown(): void
	{
		session()->flush();
		parent::tearDown();
	}

	public function testRequestOptionsRoundTrip(): void
	{
		$store = resolve(CeremonyStore::class);
		$options = $this->requestOptions('challenge-request-0001');

		$store->putRequestOptions($options);
		$pulled = $store->pullRequestOptions();

		self::assertSame('challenge-request-0001', $pulled->challenge);
		self::assertSame('localhost', $pulled->rpId);
		self::assertSame('credential-id', $pulled->allowCredentials[0]->id);
		self::assertSame('preferred', $pulled->userVerification);
	}

	public function testCreationOptionsRoundTrip(): void
	{
		$store = resolve(CeremonyStore::class);

		$store->putCreationOptions($this->creationOptions('challenge-create-0001'));
		$pulled = $store->pullCreationOptions();

		self::assertSame('challenge-create-0001', $pulled->challenge);
		self::assertSame('user-handle-bytes', $pulled->user->id);
	}

	public function testOptionsAreSingleUse(): void
	{
		$store = resolve(CeremonyStore::class);
		$store->putRequestOptions($this->requestOptions('challenge-request-0002'));
		$store->pullRequestOptions();

		$this->expectException(WebAuthnVerificationException::class);
		$this->expectExceptionMessage('No pending WebAuthn challenge');
		$store->pullRequestOptions();
	}

	public function testMissingOptionsAreRejected(): void
	{
		$this->expectException(WebAuthnVerificationException::class);
		$this->expectExceptionMessage('No pending WebAuthn challenge');
		resolve(CeremonyStore::class)->pullCreationOptions();
	}

	public function testExpiredOptionsAreRejectedAndConsumed(): void
	{
		$store = resolve(CeremonyStore::class);
		$store->putRequestOptions($this->requestOptions('challenge-request-0003'));

		$this->travel(61)->seconds();

		try {
			$store->pullRequestOptions();
			self::fail('Expired options must be rejected.');
		} catch (WebAuthnVerificationException $e) {
			self::assertStringContainsString('expired', $e->getMessage());
		}

		$this->expectException(WebAuthnVerificationException::class);
		$this->expectExceptionMessage('No pending WebAuthn challenge');
		$store->pullRequestOptions();
	}

	public function testCreationAndRequestSlotsAreSeparate(): void
	{
		$store = resolve(CeremonyStore::class);
		$store->putCreationOptions($this->creationOptions('challenge-create-0002'));
		$store->putRequestOptions($this->requestOptions('challenge-request-0004'));

		self::assertSame('challenge-request-0004', $store->pullRequestOptions()->challenge);
		self::assertSame('challenge-create-0002', $store->pullCreationOptions()->challenge);
	}

	private function requestOptions(string $challenge): PublicKeyCredentialRequestOptions
	{
		return PublicKeyCredentialRequestOptions::create(
			challenge: $challenge,
			rpId: 'localhost',
			allowCredentials: [PublicKeyCredentialDescriptor::create(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 'credential-id')],
			userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
			timeout: 60000,
		);
	}

	private function creationOptions(string $challenge): PublicKeyCredentialCreationOptions
	{
		return PublicKeyCredentialCreationOptions::create(
			rp: PublicKeyCredentialRpEntity::create('Lychee', 'localhost'),
			user: PublicKeyCredentialUserEntity::create('admin', 'user-handle-bytes', 'Admin'),
			challenge: $challenge,
			timeout: 60000,
		);
	}
}
