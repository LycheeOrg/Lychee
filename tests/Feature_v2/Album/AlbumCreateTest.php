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

class AlbumCreateTest extends BaseApiWithDataTest
{
	public function testCreateAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album', [
			'parent_id' => null,
			'title' => 'test',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		$this->assertForbidden($response);
	}

	public function testCreateAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();
		$this->assertEquals(1, Statistics::where('album_id', $new_album_id)->count());

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($new_album_id);
	}

	public function testCreateAlbumAuthorizedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($new_album_id);
		$this->assertEquals(1, Statistics::where('album_id', $new_album_id)->count());
	}
}