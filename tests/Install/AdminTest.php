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

namespace Tests\Install;

use App\Http\Middleware\AdminUserStatus;
use App\Http\Middleware\InstallationStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class AdminTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function setUp(): void
	{
		parent::setUp();
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	public function testGet(): void
	{
		$response = $this->get('install/admin');
		$this->assertOk($response);

		$response = $this->withoutMiddleware(InstallationStatus::class)->get('install/admin');
		$this->assertOk($response);

		$response = $this->withoutMiddleware([InstallationStatus::class, AdminUserStatus::class])->get('install/admin');
		$this->assertOk($response);
	}

	public function testPost(): void
	{
		$response = $this->post('install/admin');
		/** @disregard */
		self::assertEquals(422, $response->baseResponse->exception->status);
		$this->assertRedirect($response);

		$response = $this->withoutMiddleware([InstallationStatus::class, AdminUserStatus::class])->post('install/admin', [
			'username' => 'admin',
			'password' => 'admin',
			'password_confirmation' => 'admin',
		]);
		$this->assertOk($response);
		$response->assertViewIs('install.setup-success');

		$response = $this->withoutMiddleware([InstallationStatus::class, AdminUserStatus::class])->post('install/admin', [
			'username' => 'admin',
			'password' => 'admin',
			'password_confirmation' => 'admin',
		]);
		$this->assertOk($response);
		$response->assertViewIs('install.setup-admin');
	}
}