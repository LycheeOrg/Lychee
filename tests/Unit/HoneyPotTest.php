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

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\AbstractTestCase;

/**
 * Consider refactoring this test to only check the pipes used rather than the full path each time.
 */
class HoneyPotTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function setUp(): void
	{
		parent::setUp();
		// Create an admin user so that the check does not complain admin is not found.
		User::factory()->may_administrate()->create();
	}

	// ! Very slow test.
	public function testRoutesWithHoney(): void
	{
		foreach (config('honeypot.paths') as $path) {
			$response = $this->get($path);
			$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
			$response = $this->post($path);
			$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
		}

		// We check one of the version from the xpaths cross product
		$response = $this->get('admin.asp');
		$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
	}

	public function testRoutesWithoutHoney(): void
	{
		$response = $this->get('/something');
		$this->assertStatus($response, Response::HTTP_NOT_FOUND);
	}

	// ! Very slow test.
	public function testDisabled(): void
	{
		config(['honeypot.enabled' => false]);
		foreach (config('honeypot.paths') as $path) {
			$response = $this->get($path);
			$this->assertStatus($response, Response::HTTP_NOT_FOUND);
			$response = $this->post($path);
			$this->assertStatus($response, Response::HTTP_NOT_FOUND);
		}
	}
}