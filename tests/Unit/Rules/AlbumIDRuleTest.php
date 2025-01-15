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

use App\Constants\RandomID;
use App\Rules\AlbumIDRule;
use Tests\AbstractTestCase;

class AlbumIDRuleTest extends AbstractTestCase
{
	public function testNegative(): void
	{
		$rule = new AlbumIDRule(false);
		$msg = '';
		$rule->validate('attr', null, function ($message) use (&$msg) { $msg = $message; });
		$expected = ':attribute must be a string with ' .
			RandomID::ID_LENGTH . ' characters or one of the built-in IDs unsorted, starred, recent, on_this_day';

		self::assertEquals($expected, $msg);
	}
}