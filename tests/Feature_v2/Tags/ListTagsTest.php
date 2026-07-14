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

	public function testGetTagsSplitCounts(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Tags');
		$this->assertOk($response);
		$tags = collect($response->json()['tags']);
		$tag_test_resource = $tags->firstWhere('name', $this->tag_test->name);
		$this->assertEquals(1, $tag_test_resource['num_photos']);
		$this->assertEquals(0, $tag_test_resource['num_albums']);
	}

	public function testGetTagsAlbumOnlyTagIsVisibleToOwner(): void
	{
		// `roadtrip` is attached only to album1 (owned by userMayUpload1), no photos anywhere.
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		$this->album1->tags()->sync([$roadtrip->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Tags');
		$this->assertOk($response);
		$tags = collect($response->json()['tags']);
		$roadtrip_resource = $tags->firstWhere('name', 'roadtrip');
		$this->assertNotNull($roadtrip_resource);
		$this->assertEquals(0, $roadtrip_resource['num_photos']);
		$this->assertEquals(1, $roadtrip_resource['num_albums']);
	}

	public function testGetTagsAlbumOnlyTagIsHiddenFromOtherNonAdmin(): void
	{
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		$this->album1->tags()->sync([$roadtrip->id]);

		// userMayUpload2 does not own album1 and has no access to it.
		$response = $this->actingAs($this->userMayUpload2)->getJson('Tags');
		$this->assertOk($response);
		$tags = collect($response->json()['tags']);
		$this->assertNull($tags->firstWhere('name', 'roadtrip'));
	}

	public function testGetTagsAlbumOnlyTagIsVisibleToAdmin(): void
	{
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		$this->album1->tags()->sync([$roadtrip->id]);

		$response = $this->actingAs($this->admin)->getJson('Tags');
		$this->assertOk($response);
		$tags = collect($response->json()['tags']);
		$roadtrip_resource = $tags->firstWhere('name', 'roadtrip');
		$this->assertNotNull($roadtrip_resource);
		$this->assertEquals(1, $roadtrip_resource['num_albums']);
	}
}
