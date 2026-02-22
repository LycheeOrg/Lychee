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

namespace Tests\Feature_v2\Frame;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FrameTest extends BaseApiWithDataTest
{
	public function testErrors(): void
	{
		$response = $this->getJson('Frame');
		$this->assertUnauthorized($response);

		$response = $this->getJsonWithData('Frame', ['album_id' => null]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Frame', ['album_id' => null]);
		$this->assertInternalServerError($response);
	}

	public function testGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'timeout' => 30,
		]);
	}

	public function testException(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => null]);
		$this->assertStatus($response, 500);
		$response->assertSee('PhotoCollectionEmptyException');
	}

	public function testRandom(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::highlight', [
			'photo_ids' => [$this->photo1->id],
			'is_highlighted' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::random', ['album_id' => null]);
		$this->assertOk($response);
	}
}