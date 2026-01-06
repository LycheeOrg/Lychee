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
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ListTagsTest extends BaseApiWithDataTest
{
	public function testGetTagsGuest(): void
	{
		$response = $this->getJson('Tags');
		$this->assertUnauthorized($response);
	}

	public function testGetTagsUserWithoutUploadRight(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJson('Tags');
		$this->assertOk($response);
	}

	public function testGetTagsUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Tags');
		$this->assertOk($response);
	}

	public function testGetTagAdvanced(): void
	{
		// Create test tags for listing
		$test_2 = Tag::create(['name' => 'test_2']);

		// Associate the source tag with a test photo
		DB::table('photos_tags')->insert([
			'photo_id' => $this->photo2->id,
			'tag_id' => $test_2->id,
		]);

		// We make sure that test_2 is not leaked to user 1: it is attached to a photo of user 2
		$response = $this->actingAs($this->userMayUpload1)->getJson('Tags');
		$this->assertOk($response);
		$this->assertCount(1, $response->json()['tags']);
		$this->assertEquals($this->tag_test->name, $response->json()['tags'][0]['name']);

		// Admin sees everything.
		$response = $this->actingAs($this->admin)->getJson('Tags');
		$this->assertOk($response);
		$this->assertCount(2, $response->json()['tags']);
	}
}
