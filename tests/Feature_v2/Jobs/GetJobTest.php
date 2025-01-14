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

namespace Tests\Feature_v2\Jobs;

use Tests\Feature_v2\Base\BaseApiV2Test;

class GetJobTest extends BaseApiV2Test
{
	public function testGetJobsGuest(): void
	{
		$response = $this->getJson('Jobs');
		$this->assertUnauthorized($response);
	}

	public function testGetJobsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Jobs');
		$this->assertForbidden($response);
	}

	public function testGetJobsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Jobs');
		$this->assertOk($response);
	}
}