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

use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiV2Test;

class UserSpaceTest extends BaseApiV2Test
{
	public function testUserSpaceUnauthorized(): void
	{
		$response = $this->getJson('Statistics::userSpace');
		$this->assertSupporterRequired($response);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->getJson('Statistics::userSpace');
		$this->assertUnauthorized($response);
	}

	public function testUserSpaceAuthorized(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->userMayUpload1)->getJson('Statistics::userSpace');
		$this->assertOk($response);
		self::assertCount(1, $response->json());
		self::assertEquals($this->userMayUpload1->username(), $response->json()[0]['username']);
	}

	public function testUserSpaceAdmin(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->getJson('Statistics::userSpace');
		$this->assertOk($response);
		// We have 5 registered users during the tests.
		self::assertCount(5, $response->json());
	}
}