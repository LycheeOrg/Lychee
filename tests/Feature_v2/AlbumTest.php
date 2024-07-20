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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumTest extends BaseApiV2Test
{
	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testGet(): void
	{
		$response = $this->getJson('Album::get');
		$response->assertUnprocessable();
		$response->assertJson([
			'message' => 'The album i d field is required.',
		]);
	}
}