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

use App\Exceptions\Internal\LycheeLogicException;
use App\Rules\FileUuidRule;
use Tests\AbstractTestCase;

class FileUuidRuleTest extends AbstractTestCase
{
	public function testException(): void
	{
		$this->expectException(LycheeLogicException::class);

		$rule = new FileUuidRule();
		$rule->setData([]);
		$msg = '';
		$rule->validate('attr', null, function ($message) use (&$msg) { $msg = $message; });
	}

	public function testNegative(): void
	{
		$rule = new FileUuidRule();

		// First silent fail: chunk_number = 0
		$rule->setData([]);
		$msg = "don't worry";
		$rule->validate('uuid_name', null, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		// Second silent fail: chunk_number = 1
		$rule->setData(['chunk_number' => 1]);
		$msg = "don't worry";
		$rule->validate('uuid_name', null, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule->setData(['chunk_number' => 1]);
		$msg = "don't worry";
		$rule->validate('uuid_name', 'not null', function ($message) use (&$msg) { $msg = $message; });
		$expected = 'Error: Expected NULL in :attribute , got not null.';
		self::assertEquals($expected, $msg);

		$rule->setData(['chunk_number' => 2]);
		$msg = "don't worry";
		$rule->validate('uuid_name', 123456, function ($message) use (&$msg) { $msg = $message; });
		$expected = ':attribute is not a string.';
		self::assertEquals($expected, $msg);

		// Third silent fail
		$rule->setData(['chunk_number' => 2]);
		$msg = "don't worry";
		$rule->validate('uuid_name', '1234567890.jpg', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule->setData(['chunk_number' => 2, 'file_name' => 'file.png']);
		$msg = "don't worry";
		$rule->validate('uuid_name', '.jpg', function ($message) use (&$msg) { $msg = $message; });
		$expected = ':attribute is not a valid random string.';
		self::assertEquals($expected, $msg);

		$rule->setData(['chunk_number' => 2, 'file_name' => 'file.png']);
		$msg = "don't worry";
		$rule->validate('uuid_name', '1234567890123456.png', function ($message) use (&$msg) { $msg = $message; });
		$expected = ':attribute is not a valid target file.';
		self::assertEquals($expected, $msg);
	}
}