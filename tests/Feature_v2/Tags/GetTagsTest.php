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
}
