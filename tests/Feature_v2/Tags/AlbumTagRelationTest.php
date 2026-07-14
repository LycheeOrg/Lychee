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

/**
 * Unit-style relation tests for the new Album <-> Tag pivot (`albums_tags`),
 * introduced by Feature 050 (Album Tags).
 */
class AlbumTagRelationTest extends BaseApiWithDataTest
{
	public function testAlbumTagsRelationRoundTrips(): void
	{
		$tag = Tag::factory()->with_name('vacation')->create();

		$this->album1->tags()->sync([$tag->id]);

		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $tag->id,
		]);

		$fresh_album = $this->album1->fresh();
		$this->assertCount(1, $fresh_album->tags);
		$this->assertEquals($tag->id, $fresh_album->tags->first()->id);
		$this->assertEquals('vacation', $fresh_album->tags->first()->name);
	}

	public function testTagAlbumsInverseRelationRoundTrips(): void
	{
		$tag = Tag::factory()->with_name('greece')->create();

		$this->album1->tags()->sync([$tag->id]);

		$fresh_tag = $tag->fresh();
		$this->assertCount(1, $fresh_tag->albums);
		$this->assertEquals($this->album1->id, $fresh_tag->albums->first()->id);
	}

	public function testAlbumTagsAreIndependentFromTagAlbumTags(): void
	{
		// tagAlbum1 is already linked to tag_test via tag_albums_tags (fixture setup).
		// Attaching the same tag as an *album* tag on a completely different album
		// (album2) must not create any relationship with tagAlbum1's criteria tags,
		// and must not appear in tag_albums_tags.
		$this->album2->tags()->sync([$this->tag_test->id]);

		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album2->id,
			'tag_id' => $this->tag_test->id,
		]);
		$this->assertDatabaseMissing('tag_albums_tags', [
			'album_id' => $this->album2->id,
			'tag_id' => $this->tag_test->id,
		]);

		// tagAlbum1's own criteria tags are unaffected.
		$this->assertCount(1, $this->tagAlbum1->fresh()->tags);
	}
}
