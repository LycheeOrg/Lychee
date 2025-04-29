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

namespace Tests\Feature_v2\Album;

use App\Models\Statistics;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumCreateTagTest extends BaseApiWithDataTest
{
	public function testCreateTagAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('TagAlbum', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('TagAlbum', [
			'title' => 'test_tag',
			'tags' => ['tag1', 'tag2'],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->postJson('TagAlbum', [
			'title' => 'test_tag',
			'tags' => ['tag1', 'tag2'],
		]);
		$this->assertForbidden($response);
	}

	public function testCreateTagAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('TagAlbum', [
			'title' => 'test_tag',
			'tags' => ['tag1', 'tag2'],
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();
		$this->assertEquals(1, Statistics::where('album_id', $new_album_id)->count());

		$response = $this->getJsonWithData('Albums');
		$this->assertOk($response);
		$response->assertSee($new_album_id);
	}
}