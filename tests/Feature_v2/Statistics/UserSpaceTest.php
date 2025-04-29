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

namespace Tests\Feature_v2\Statistics;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UserSpaceTest extends BaseApiWithDataTest
{
	public function testUserSpaceUnauthorized(): void
	{
		$response = $this->getJson('Statistics::userSpace');
		$this->assertSupporterRequired($response);

		$this->requireSe();
		$response = $this->getJson('Statistics::userSpace');
		$this->assertUnauthorized($response);
	}

	public function testUserSpaceAuthorized(): void
	{
		$this->requireSe();
		$response = $this->actingAs($this->userMayUpload1)->getJson('Statistics::userSpace');
		$this->assertOk($response);
		self::assertCount(1, $response->json());
		self::assertEquals($this->userMayUpload1->username(), $response->json()[0]['username']);
	}

	public function testUserSpaceAdmin(): void
	{
		$this->requireSe();
		$response = $this->actingAs($this->admin)->getJson('Statistics::userSpace');
		$this->assertOk($response);
		// We have 5 registered users during the tests.
		self::assertCount(5, $response->json());
	}
}