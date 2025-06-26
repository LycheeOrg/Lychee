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

namespace Tests\Feature_v2\Tags;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ListTagsTest extends BaseApiWithDataTest
{
	public function testGetTagsGuest(): void
	{
		$response = $this->getJson('Tag');
		$this->assertUnauthorized($response);
	}

	public function testGetTagsUserWithoutUploadRight(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJson('Tag');
		$this->assertForbidden($response);
	}

	public function testGetTagsUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Tag');
		$this->assertOk($response);
	}
}
