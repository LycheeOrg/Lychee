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

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class OpenApiTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function setUp(): void
	{
		parent::setUp();
		// Create an admin user so that the check does not complain admin is not found.
		User::factory()->may_administrate()->create();
	}

	/**
	 * Test the OpenApi documentation is generated.
	 *
	 * @return void
	 */
	public function testOpenApi(): void
	{
		$response = $this->get('/docs/api');
		$this->assertOk($response);
	}
}
