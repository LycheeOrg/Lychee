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

namespace Tests\Feature_v2\Tags;

use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class GetTagsTest extends BaseApiWithDataTest
{
	public function testGetTagGuest(): void
	{
		$response = $this->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetTagLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertCount(0, $data['photos']);
	}

	public function testGetTagUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertEquals($data['photos'][0]['id'], $this->photo1->id);
	}

	public function testGetTagUserWithShared(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		// Verify the response structure contains tag name and photos
		$data = $response->json();
		$this->assertArrayHasKey('name', $data);
		$this->assertArrayHasKey('photos', $data);
		$this->assertEquals($this->tag_test->name, $data['name']);
		$this->assertIsArray($data['photos']);
		$this->assertCount(0, $data['photos']);
	}

	public function testGetTagWithMissingTagId(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Tag');
		$this->assertUnprocessable($response);
	}

	public function testGetNonExistentTag(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Tag', ['tag_id' => '999999']);
		$this->assertNotFound($response);
	}

	public function testGetTagIncludesAccessibleAlbums(): void
	{
		// tag_test is already attached to photo1; also attach it to album1 (same owner).
		$this->album1->tags()->sync([$this->tag_test->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('albums', $data);
		$this->assertIsArray($data['albums']);
		$this->assertCount(1, $data['albums']);
		$this->assertEquals($this->album1->id, $data['albums'][0]['id']);

		// photos are still returned as before.
		$this->assertCount(1, $data['photos']);
	}

	public function testGetTagAlbumsEmptyArrayWhenNoneAccessible(): void
	{
		// tag_test has photo1 but no album.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('albums', $data);
		$this->assertIsArray($data['albums']);
		$this->assertCount(0, $data['albums']);
	}

	public function testGetTagAlbumsScopedToAccessibleUser(): void
	{
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		// album2 is owned by userMayUpload2 and not shared with userMayUpload1
		// (unlike album1, which grants userMayUpload2 access via the `perm1` fixture).
		$this->album2->tags()->sync([$roadtrip->id]);

		// Owner sees the album.
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Tag', ['tag_id' => $roadtrip->id]);
		$this->assertOk($response);
		$this->assertCount(1, $response->json('albums'));

		// A different, unrelated non-admin does not see it.
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Tag', ['tag_id' => $roadtrip->id]);
		$this->assertOk($response);
		$this->assertCount(0, $response->json('albums'));
	}
}
