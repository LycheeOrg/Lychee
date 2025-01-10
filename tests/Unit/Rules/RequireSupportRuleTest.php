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

use App\Rules\BooleanRequireSupportRule;
use App\Rules\IntegerRequireSupportRule;
use App\Rules\StringRequireSupportRule;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSupport;

class RequireSupportRuleTest extends AbstractTestCase
{
	use RequireSupport;

	public function testNegative(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getFree(), expected: true);
		$msg = "don't worry";
		$rule->validate('', true, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getFree(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'something', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getFree(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 1, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testRequireSupport(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getFree(), expected: true);
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getFree(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getFree(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 3, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);
	}

	public function testIsSupportNegative(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getSupporter(), expected: true);
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getSupporter(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getSupporter(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testIsSupport(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getSupporter(), expected: true);
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getSupporter(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getSupporter(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 3, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}
}