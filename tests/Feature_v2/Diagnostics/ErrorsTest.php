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

namespace Tests\Feature_v2\Diagnostics;

use Tests\Feature_v2\Base\BaseApiV2Test;

class ErrorsTest extends BaseApiV2Test
{
	public function testGetGuest(): void
	{
		$response = $this->getJson('Diagnostics');
		$this->assertOk($response);
	}
}