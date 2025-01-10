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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class UsersTest extends BaseApiV2Test
{
	public function testGetGuest(): void
	{
		$response = $this->getJson('Users');
		$this->assertUnauthorized($response);
	}

	public function testGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Users');
		$this->assertOk($response);
	}

	public function testGetCountGuest(): void
	{
		$response = $this->getJson('Users::count');
		$this->assertUnauthorized($response);
	}

	public function testGetCount(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Users::count');
		$this->assertOk($response);
	}
}