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

use App\Rules\DescriptionRule;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

/**
 * The description limit comes from `features.description-max-length`.
 */
class DescriptionRuleTest extends AbstractTestCase
{
	public function testMaxLengthFollowsConfig(): void
	{
		Config::set('features.description-max-length', 5000);
		self::assertSame(5000, DescriptionRule::maxLength());
	}

	public function testAtLimitPasses(): void
	{
		Config::set('features.description-max-length', 10);
		self::assertTrue((new DescriptionRule())->passes('description', str_repeat('a', 10)));
	}

	public function testOverLimitFails(): void
	{
		Config::set('features.description-max-length', 10);
		self::assertFalse((new DescriptionRule())->passes('description', str_repeat('a', 11)));
	}

	public function testNullPasses(): void
	{
		self::assertTrue((new DescriptionRule())->passes('description', null));
	}

	public function testMessageNamesLimit(): void
	{
		Config::set('features.description-max-length', 10);
		self::assertSame(
			':attribute must be either null or a non-empty string with at most 10 characters',
			(new DescriptionRule())->message()
		);
	}
}
