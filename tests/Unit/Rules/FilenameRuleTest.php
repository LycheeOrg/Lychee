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

use App\Rules\FilenameRule;
use Tests\AbstractTestCase;

class FilenameRuleTest extends AbstractTestCase
{
	private function assertPasses(mixed $value): void
	{
		$rule = new FilenameRule();
		$failed = false;
		$rule->validate('file_name', $value, function () use (&$failed): void { $failed = true; });
		self::assertFalse($failed, 'Expected "' . var_export($value, true) . '" to pass but it failed.');
	}

	private function assertFails(mixed $value, ?string $expected_message = null): void
	{
		$rule = new FilenameRule();
		$msg = null;
		$rule->validate('file_name', $value, function ($message) use (&$msg): void { $msg = $message; });
		self::assertNotNull($msg, 'Expected "' . var_export($value, true) . '" to fail but it passed.');
		if ($expected_message !== null) {
			self::assertSame($expected_message, $msg);
		}
	}

	public function testValidFilenames(): void
	{
		$this->assertPasses('test.jpg');
		$this->assertPasses('IMG_1234.png');
		$this->assertPasses('.hidden');
		$this->assertPasses('file with spaces.jpg');
		$this->assertPasses('café.jpg');
		$this->assertPasses('a');
		$this->assertPasses('file.tar.gz');
	}

	public function testNonStringValues(): void
	{
		$this->assertFails(123, ':attribute is not a string.');
		$this->assertFails(1.5, ':attribute is not a string.');
		$this->assertFails(true, ':attribute is not a string.');
		$this->assertFails(null, ':attribute is not a string.');
		$this->assertFails([], ':attribute is not a string.');
		$this->assertFails(['file.jpg'], ':attribute is not a string.');
	}

	public function testContainsDirectory(): void
	{
		$this->assertFails('foo/bar.jpg', ':attribute contains a directory, give proper filename.');
		$this->assertFails('/etc/passwd', ':attribute contains a directory, give proper filename.');
		$this->assertFails('/file.jpg', ':attribute contains a directory, give proper filename.');
		$this->assertFails('a/b/c.jpg', ':attribute contains a directory, give proper filename.');
	}

	public function testPathTraversalAttempts(): void
	{
		$this->assertFails('.');
		$this->assertFails('../secret.jpg');
		$this->assertFails('test/Samples/../secret.jpg');
		$this->assertFails('../../etc/passwd');
		$this->assertFails('..\\..\\windows\\win.ini');
		$this->assertFails('foo\\bar.jpg');
		$this->assertFails('foo/../bar.jpg');
	}

	public function testDoubleDotWithoutSeparatorStillFails(): void
	{
		// ".." alone is not treated as a directory by pathinfo(), but it must
		// still be rejected because it is caught by the invalid-characters check.
		$this->assertFails('..');
		$this->assertFails('a..b.jpg');
	}

	public function testEmptyStringPasses(): void
	{
		// pathinfo('', PATHINFO_DIRNAME) === '' and it contains none of the
		// forbidden substrings, so the rule itself does not flag it. Emptiness
		// should be rejected by a separate `required` rule upstream.
		$this->assertPasses('');
	}
}
