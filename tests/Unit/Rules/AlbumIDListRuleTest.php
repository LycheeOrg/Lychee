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

use App\Constants\RandomID;
use App\Rules\AlbumIDListRule;
use Tests\AbstractTestCase;

class AlbumIDListRuleTest extends AbstractTestCase
{
	public function testNegative(): void
	{
		$rule = new AlbumIDListRule();
		$msg = '';
		$rule->validate('attr', null, function ($message) use (&$msg): void { $msg = $message; });
		$expected = ':attribute must be a comma-separated string of strings with either ' .
			RandomID::ID_LENGTH . ' characters each or one of the built-in IDs unsorted, highlighted, recent, on_this_day, untagged, unrated, one_star, two_stars, three_stars, four_stars, five_stars, best_pictures, my_rated_pictures, my_best_pictures';

		self::assertEquals($expected, $msg);
	}
}