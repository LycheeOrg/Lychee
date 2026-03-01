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

use App\Enum\TimelineAlbumGranularity;
use App\Rules\EnumRequireSupportRule;
use LycheeVerify\Contract\VerifyInterface;
use Tests\AbstractTestCase;

class EnumRequireSupportRuleTest extends AbstractTestCase
{
	public function testHappy(): void
	{
		$verify = $this->createMock(VerifyInterface::class);
		$verify->method('check')->willReturn(false);

		/** @disregard P1006 */
		$rule = new EnumRequireSupportRule(
			type: TimelineAlbumGranularity::class,
			expected: [TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED],
			verify: $verify,
		);

		$rule->validate('something', 'default', fn ($_msg) => self::fail());
		self::assertTrue(true);
	}

	public function testUnhappy(): void
	{
		self::expectException(\Exception::class);

		$verify = $this->createMock(VerifyInterface::class);
		$verify->method('check')->willReturn(false);

		/** @disregard P1006 */
		$rule = new EnumRequireSupportRule(
			type: TimelineAlbumGranularity::class,
			expected: [TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED],
			verify: $verify,
		);

		$rule->validate('something', 'year', fn ($_msg) => throw new \Exception('Should not be called'));
	}

	public function testHappy2(): void
	{
		$verify = $this->createMock(VerifyInterface::class);
		$verify->method('check')->willReturn(true);

		/** @disregard P1006 */
		$rule = new EnumRequireSupportRule(
			type: TimelineAlbumGranularity::class,
			expected: [TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED],
			verify: $verify,
		);

		$rule->validate('something', 'year', fn ($_msg) => throw new \Exception('Should not be called'));
		self::assertTrue(true);
	}

	public function testHappy3(): void
	{
		$verify = $this->createMock(VerifyInterface::class);
		$verify->method('check')->willReturn(true);

		/** @disregard P1006 */
		$rule = new EnumRequireSupportRule(
			type: TimelineAlbumGranularity::class,
			expected: [TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED],
			verify: $verify,
		);

		$rule->validate('something', null, fn ($_msg) => throw new \Exception('Should not be called'));
		self::assertTrue(true);
	}

	public function testHappy4(): void
	{
		$verify = $this->createMock(VerifyInterface::class);
		$verify->method('check')->willReturn(false);

		/** @disregard P1006 */
		$rule = new EnumRequireSupportRule(
			type: TimelineAlbumGranularity::class,
			expected: [TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED],
			verify: $verify,
		);

		// Type error...
		$rule->validate('something', $this, fn ($_msg) => throw new \Exception('Should not be called'));
		self::assertTrue(true);
	}
}