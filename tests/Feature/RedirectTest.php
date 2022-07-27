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

class RedirectTest extends TestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testRedirection(): void
	{
		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa');

		$response->assertStatus(302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa');

		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');

		$response->assertStatus(302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');
	}
}
