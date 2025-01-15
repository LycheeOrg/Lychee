<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

use App\Rules\ConfigKeyRequireSupportRule;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSupport;

class ConfigKeyRequireSupportRuleTest extends AbstractTestCase
{
	use RequireSupport;

	public function testNegative(): void
	{
		$rule = new ConfigKeyRequireSupportRule(verify: $this->getFree());
		$msg = "don't worry";
		$rule->validate('', 'version', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$msg = "don't worry";
		$rule->validate('', 1234567, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$msg = "don't worry";
		$rule->validate('', 'not_a_real_key', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testRequireSupport(): void
	{
		$rule = new ConfigKeyRequireSupportRule(verify: $this->getFree());
		$msg = "don't worry";
		$rule->validate('', 'default_user_quota', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);
	}

	public function testIsSupport(): void
	{
		$rule = new ConfigKeyRequireSupportRule(verify: $this->getSupporter());
		$msg = "don't worry";
		$rule->validate('', 'default_user_quota', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}
}