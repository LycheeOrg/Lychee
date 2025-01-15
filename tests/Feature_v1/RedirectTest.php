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

namespace Tests\Feature_v1;

use Tests\AbstractTestCase;

class RedirectTest extends AbstractTestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testRedirection(): void
	{
		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa');

		$this->assertStatus($response, 302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa');

		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');

		$this->assertStatus($response, 302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');
	}
}
