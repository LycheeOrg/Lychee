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

namespace Tests\Feature_v2;

use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Test Album::tags endpoint - returns list of tags available in an album.
 *
 * Scenarios covered:
 * - S-026-11: Album with tagged photos returns distinct sorted tags
 * - S-026-12: Non-existent album returns 404
 * - S-026-13: Private album without access returns 403
 * - Album with no tagged photos returns empty array
 * - S-026-19: TagAlbum returns tags from photos in that TagAlbum
 * - S-026-20: Smart Album returns tags from photos in computed photo set
 */
class AlbumTagsControllerTest extends BaseApiWithDataTest
{
	public function testAlbumWithTaggedPhotosReturnsDistinctSortedTags(): void
	{
		// S-026-11: Album with tagged photos returns distinct sorted tags
		$this->actingAs($this->userMayUpload1);

		// Create additional tags and attach them to photos in album1
		$tag2 = Tag::factory()->create(['name' => 'Zebra Tag']); // Will be last alphabetically
		$tag3 = Tag::factory()->create(['name' => 'Apple Tag']); // Will be first alphabetically

		// Attach tags to photos (photo1 already has 'test' tag from BaseApiWithDataTest)
		$this->photo1->tags()->attach($tag2->id);
		$this->photo1b->tags()->attach($tag3->id);

		// album1 now has photos with tags: 'test', 'Zebra Tag', 'Apple Tag'
		$response = $this->getJsonWithData('Album::tags', ['album_id' => $this->album1->id]);

		$this->assertOk($response);
		$tags = $response->json('tags');

		// Should return 3 distinct tags
		$this->assertCount(3, $tags);

		// Verify tags are sorted alphabetically by name
		$this->assertEquals('Apple Tag', $tags[0]['name']);
		$this->assertEquals('test', $tags[1]['name']);
		$this->assertEquals('Zebra Tag', $tags[2]['name']);

		// Verify each tag has required fields
		$this->assertArrayHasKey('id', $tags[0]);
		$this->assertArrayHasKey('name', $tags[0]);
		$this->assertArrayHasKey('description', $tags[0]);
	}

	public function testNonExistentAlbumReturns404(): void
	{
		// S-026-12: Non-existent album returns 404
		$this->actingAs($this->userMayUpload1);

		// Use a valid 24-character album ID that doesn't exist in the database
		$response = $this->getJsonWithData('Album::tags', ['album_id' => '000000000000000000000000']);

		$this->assertNotFound($response);
	}

	public function testPrivateAlbumWithoutAccessReturns403(): void
	{
		// S-026-13: Private album without access returns 403
		// album2 belongs to userMayUpload2, userMayUpload1 should not have access
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonWithData('Album::tags', ['album_id' => $this->album2->id]);

		$this->assertForbidden($response);
	}

	public function testAlbumWithNoTaggedPhotosReturnsEmptyArray(): void
	{
		// Album with no tagged photos returns empty array
		$this->actingAs($this->userMayUpload2);

		// album2 has photos but they don't have tags attached
		$response = $this->getJsonWithData('Album::tags', ['album_id' => $this->album2->id]);

		$this->assertOk($response);
		$tags = $response->json('tags');

		$this->assertIsArray($tags);
		$this->assertCount(0, $tags);
	}

	public function testTagAlbumReturnsTagsFromPhotos(): void
	{
		// S-026-19: TagAlbum returns tags from photos in that TagAlbum
		$this->actingAs($this->userMayUpload1);

		// tagAlbum1 contains photos with 'test' tag
		// Add another tag to photo1
		$tag2 = Tag::factory()->create(['name' => 'Another Tag']);
		$this->photo1->tags()->attach($tag2->id);

		$response = $this->getJsonWithData('Album::tags', ['album_id' => $this->tagAlbum1->id]);

		$this->assertOk($response);
		$tags = $response->json('tags');

		// Should return tags from photos in this TagAlbum
		$this->assertGreaterThanOrEqual(1, count($tags));
		$tagNames = array_column($tags, 'name');
		$this->assertContains('test', $tagNames);
	}

	public function testSmartAlbumReturnsTagsFromComputedPhotoSet(): void
	{
		// S-026-20: Smart Album returns tags from photos in computed photo set
		$this->actingAs($this->userMayUpload1);

		// Test with a smart album (e.g., 'unsorted')
		$response = $this->getJsonWithData('Album::tags', ['album_id' => 'unsorted']);

		// Smart albums should work, but might return empty if no photos have tags
		// The key is that it doesn't error out
		$this->assertOk($response);
		$tags = $response->json('tags');
		$this->assertIsArray($tags);
	}
}
