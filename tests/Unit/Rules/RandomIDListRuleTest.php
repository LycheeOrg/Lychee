<?php

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
use App\Rules\RandomIDListRule;
use Tests\AbstractTestCase;

class RandomIDListRuleTest extends AbstractTestCase
{
	public function testNegative(): void
	{
		$rule = new RandomIDListRule();
		$msg = $rule->message();
		$expected = ':attribute must be a comma-separated string of strings with ' . RandomID::ID_LENGTH . ' characters each.';

		self::assertEquals($expected, $msg);
	}
}