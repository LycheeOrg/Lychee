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

namespace Tests\Unit\Services;

use App\Services\TemporaryLinkSigner;
use Tests\AbstractTestCase;

/**
 * Unit tests for TemporaryLinkSigner (Feature 056, FR-056-04).
 *
 * Pure unit-testable: no HTTP/Laravel request plumbing, just HMAC-SHA256 of
 * the timestamp keyed by config('app.key').
 */
class TemporaryLinkSignerTest extends AbstractTestCase
{
	public function testValidMacVerifies(): void
	{
		$signer = new TemporaryLinkSigner();
		$timestamp = 1_700_000_000;

		$mac = $signer->sign($timestamp);

		self::assertTrue($signer->verify($timestamp, $mac));
	}

	public function testTamperedMacFails(): void
	{
		$signer = new TemporaryLinkSigner();
		$timestamp = 1_700_000_000;

		$mac = $signer->sign($timestamp);
		$tampered = substr($mac, 0, -1) . (str_ends_with($mac, 'a') ? 'b' : 'a');

		self::assertFalse($signer->verify($timestamp, $tampered));
	}

	public function testMacForDifferentTimestampFails(): void
	{
		$signer = new TemporaryLinkSigner();

		$mac = $signer->sign(1_700_000_000);

		self::assertFalse($signer->verify(1_700_000_001, $mac));
	}
}
