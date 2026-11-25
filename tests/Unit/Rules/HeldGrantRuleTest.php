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

namespace Tests\Unit\Rules;

use App\Rules\HeldGrantRule;
use Tests\AbstractTestCase;

/**
 * Feature 118: a sharer hands out only the grants they hold (FR-118-07).
 */
class HeldGrantRuleTest extends AbstractTestCase
{
	private function fails(bool $held, mixed $value, bool $already_granted = false): bool
	{
		$failed = false;
		(new HeldGrantRule($held, $already_granted))->validate('grants_upload', $value, function () use (&$failed): void { $failed = true; });

		return $failed;
	}

	public function testHeldGrantAcceptsEverything(): void
	{
		self::assertFalse($this->fails(true, true));
		self::assertFalse($this->fails(true, false));
		self::assertFalse($this->fails(true, '1'));
	}

	public function testLackedGrantRefusesTrue(): void
	{
		self::assertTrue($this->fails(false, true));
		self::assertTrue($this->fails(false, '1'));
		self::assertTrue($this->fails(false, 'true'));
	}

	public function testLackedGrantAcceptsFalse(): void
	{
		self::assertFalse($this->fails(false, false));
		self::assertFalse($this->fails(false, '0'));
		self::assertFalse($this->fails(false, 'false'));
	}

	/**
	 * Q-118-09: keeping a grant given earlier is not handing it out.
	 */
	public function testLackedGrantAcceptsTrueWhenAlreadyGranted(): void
	{
		self::assertFalse($this->fails(false, true, true));
		self::assertFalse($this->fails(false, '1', true));
		self::assertFalse($this->fails(false, false, true));
	}

	public function testHeldGrantIgnoresStoredValue(): void
	{
		self::assertFalse($this->fails(true, true, false));
		self::assertFalse($this->fails(true, true, true));
	}
}
