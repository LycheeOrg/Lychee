<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Feature_v2;

use App\Models\Tag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumPhotosFilterTest extends BaseApiWithDataTest
{
	protected function tearDown(): void
	{
		// Clean up tags created during tests
		Tag::where('name', 'Landscape')->delete();
		Tag::where('name', 'Portrait')->delete();
		Tag::where('name', 'Sunset')->delete();

		parent::tearDown();
	}

	public function testAlbumPhotosWithOrFilterReturnsMatchingPhotos(): void
	{
		// S-026-14: GET /Album::photos with tag_ids[] and tag_logic=OR
		$this->actingAs($this->userMayUpload1);

		// Create additional tags
		$tag2 = Tag::factory()->create(['name' => 'Landscape']);
		$tag3 = Tag::factory()->create(['name' => 'Portrait']);

		// Attach tag2 to photo1, tag3 to photo1b
		$this->photo1->tags()->attach($tag2->id); // photo1 now has 'test' and 'Landscape'
		$this->photo1b->tags()->attach($tag3->id); // photo1b has 'Portrait'

		// Filter for photos with 'Landscape' OR 'Portrait' tags
		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
			'tag_ids' => [$tag2->id, $tag3->id],
			'tag_logic' => 'OR',
		]);

		$this->assertOk($response);
		$photos = $response->json('photos');

		// Should return photo1 (has Landscape) and photo1b (has Portrait)
		$this->assertCount(2, $photos);
		$photo_ids = array_column($photos, 'id');
		$this->assertContains($this->photo1->id, $photo_ids);
		$this->assertContains($this->photo1b->id, $photo_ids);
	}

	public function testAlbumPhotosWithAndFilterReturnsOnlyPhotosWithAllTags(): void
	{
		// S-026-04: AND logic returns photos with ALL specified tags
		$this->actingAs($this->userMayUpload1);

		// Create tags
		$tag2 = Tag::factory()->create(['name' => 'Landscape']);
		$tag3 = Tag::factory()->create(['name' => 'Sunset']);

		// Attach both tags to photo1 only
		$this->photo1->tags()->attach([$tag2->id, $tag3->id]); // photo1 has 'test', 'Landscape', 'Sunset'
		$this->photo1b->tags()->attach($tag2->id); // photo1b only has 'Landscape'

		// Filter for photos with 'Landscape' AND 'Sunset'
		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
			'tag_ids' => [$tag2->id, $tag3->id],
			'tag_logic' => 'AND',
		]);

		$this->assertOk($response);
		$photos = $response->json('photos');

		// Should only return photo1 (has both tags)
		$this->assertCount(1, $photos);
		$this->assertEquals($this->photo1->id, $photos[0]['id']);
	}

	public function testAlbumPhotosWithoutTagFilterReturnsAllPhotos(): void
	{
		// S-026-16: Backward compatibility - no tag params returns all photos
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
		]);

		$this->assertOk($response);
		$photos = $response->json('photos');

		// album1 has photo1 and photo1b
		$this->assertGreaterThanOrEqual(2, count($photos));
	}

	public function testAlbumPhotosWithEmptyTagFilterReturnsAllPhotos(): void
	{
		// Empty tag_ids[] should be treated as no filter
		$this->actingAs($this->userMayUpload1);

		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
			'tag_ids' => [],
		]);

		$this->assertOk($response);
		$photos = $response->json('photos');

		// Should return all photos
		$this->assertGreaterThanOrEqual(2, count($photos));
	}
}
