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
 * Pure unit-testable: no HTTP/Laravel request plumbing, just a rotating
 * code derived from a full HMAC-SHA256 digest keyed by config('app.key').
 */
class TemporaryLinkSignerTest extends AbstractTestCase
{
	public function testValidCodeVerifies(): void
	{
		$signer = new TemporaryLinkSigner();

		$code = $signer->sign();

		self::assertTrue($signer->verify($code));
	}

	public function testTamperedCodeFails(): void
	{
		$signer = new TemporaryLinkSigner();

		$code = $signer->sign();
		$tampered = substr($code, 0, -1) . (str_ends_with($code, '1') ? '2' : '1');

		self::assertFalse($signer->verify($tampered));
	}

	public function testUnrelatedCodeFails(): void
	{
		$signer = new TemporaryLinkSigner();

		self::assertFalse($signer->verify('000000000000'));
	}
}
