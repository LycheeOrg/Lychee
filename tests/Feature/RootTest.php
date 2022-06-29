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

namespace Tests\Feature;

use Tests\TestCase;

class RootTest extends TestCase
{
	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testRoot(): void
	{
		exec('php index.php 2>&1', $return);
		$return = implode('', $return);
		static::assertStringContainsString('This is the root directory and MUST NOT BE PUBLICLY ACCESSIBLE', $return);
	}
}
