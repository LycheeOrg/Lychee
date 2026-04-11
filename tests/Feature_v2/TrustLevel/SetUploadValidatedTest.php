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

namespace Tests\Feature_v2\TrustLevel;

use App\Actions\Photo\Pipes\Shared\SetUploadValidated;
use App\Enum\UserUploadTrustLevel;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Tests that SetUploadValidated correctly handles the is_guest_upload flag,
 * which is needed when ProcessImageJob runs in a queue worker where Auth::user() is null.
 */
class SetUploadValidatedTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testGuestUploadWithCheckLevelIsNotValidated(): void
	{
		Configs::set('guest_upload_trust_level', UserUploadTrustLevel::CHECK->value);

		/** @var SetUploadValidated $pipe */
		$pipe = app(SetUploadValidated::class);
		$method = new \ReflectionMethod(SetUploadValidated::class, 'resolveIsValidated');

		// Guest upload (is_guest_upload=true) with CHECK config → not validated
		$result = $method->invoke($pipe, 0, true);
		$this->assertFalse($result, 'Guest upload with CHECK trust level should not be validated');
	}

	public function testGuestUploadWithTrustedLevelIsValidated(): void
	{
		Configs::set('guest_upload_trust_level', UserUploadTrustLevel::TRUSTED->value);

		/** @var SetUploadValidated $pipe */
		$pipe = app(SetUploadValidated::class);
		$method = new \ReflectionMethod(SetUploadValidated::class, 'resolveIsValidated');

		// Guest upload (is_guest_upload=true) with TRUSTED config → validated
		$result = $method->invoke($pipe, 0, true);
		$this->assertTrue($result, 'Guest upload with TRUSTED trust level should be validated');
	}

	public function testGuestUploadWithMonitorLevelIsValidated(): void
	{
		Configs::set('guest_upload_trust_level', UserUploadTrustLevel::MONITOR->value);

		/** @var SetUploadValidated $pipe */
		$pipe = app(SetUploadValidated::class);
		$method = new \ReflectionMethod(SetUploadValidated::class, 'resolveIsValidated');

		// Guest upload with MONITOR config → validated (monitor behaves as trusted)
		$result = $method->invoke($pipe, 0, true);
		$this->assertTrue($result, 'Guest upload with MONITOR trust level should be validated');
	}

	public function testNonGuestWithQueuedOwnerIdIsNotConfusedWithGuest(): void
	{
		// When is_guest_upload=false and intended_owner_id != 0, no guest branch applies
		Configs::set('guest_upload_trust_level', UserUploadTrustLevel::TRUSTED->value);

		/** @var SetUploadValidated $pipe */
		$pipe = app(SetUploadValidated::class);
		$method = new \ReflectionMethod(SetUploadValidated::class, 'resolveIsValidated');

		// Non-guest, non-existent owner → fail-open (return true)
		$result = $method->invoke($pipe, 999999999, false);
		$this->assertTrue($result, 'Non-guest upload with missing owner should fail-open to validated');
	}
}
