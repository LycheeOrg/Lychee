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
 * Validates that renaming/merging/deleting a Tag (and the background
 * `cleanupUnusedTags()` pass) correctly carries over -- or removes -- the
 * album associations introduced by Feature 050 (Album Tags), exactly as it
 * already does for photo and tag-album associations. Mirrors
 * {@see TagAlbumsImpactTest}.
 */
class AlbumTagsImpactTest extends BaseApiWithDataTest
{
	protected Tag $source_tag;
	protected Tag $destination_tag;

	public function setUp(): void
	{
		parent::setUp();

		$this->source_tag = Tag::create(['name' => 'source_tag_to_merge']);
		$this->destination_tag = Tag::create(['name' => 'destination_tag']);

		// album1 belongs to userMayUpload1.
		$this->album1->tags()->sync([$this->source_tag->id, $this->destination_tag->id]);
	}

	public function testCleanupDoesNotPurgeAlbumOnlyTag(): void
	{
		// `roadtrip` is attached only to album2, no photos anywhere carry it.
		$roadtrip = Tag::create(['name' => 'roadtrip']);
		$this->album2->tags()->sync([$roadtrip->id]);

		// Trigger the global cleanup pass via an unrelated tag delete.
		// `cleanupUnusedTags()` scans *all* tags, not just the deleted one,
		// so this is enough to prove album-only tags are never purged.
		$response = $this->actingAs($this->admin)->deleteJson('Tag', ['tags' => [$this->tag_test->id]]);
		$this->assertNoContent($response);

		$this->assertDatabaseHas('tags', ['id' => $roadtrip->id]);
		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album2->id,
			'tag_id' => $roadtrip->id,
		]);
	}

	public function testMergeTagsAdminMovesAlbumAssociations(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		$this->assertNull(Tag::find($this->source_tag->id));

		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->destination_tag->id,
		]);
		$this->assertDatabaseMissing('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->source_tag->id,
		]);
	}

	public function testMergeTagsNonAdminOnlyAffectsOwnAlbums(): void
	{
		// album2 (owned by userMayUpload2) is independently tagged with source_tag.
		$this->album2->tags()->sync([$this->source_tag->id]);

		// userMayUpload1 (owner of album1) merges source_tag into destination_tag.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		// album1 (user1's own album) moved to destination_tag.
		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->destination_tag->id,
		]);
		$this->assertDatabaseMissing('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->source_tag->id,
		]);

		// album2 (user2's album) remains tagged with source_tag, untouched.
		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album2->id,
			'tag_id' => $this->source_tag->id,
		]);

		// source_tag must still exist since album2 still references it.
		$this->assertNotNull(Tag::find($this->source_tag->id));
	}

	public function testDeleteTagRemovesAlbumAssociations(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => [$this->source_tag->id]]);
		$this->assertNoContent($response);

		$this->assertDatabaseMissing('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->source_tag->id,
		]);
	}

	public function testDeleteTagNonAdminOnlyAffectsOwnAlbums(): void
	{
		$this->album2->tags()->sync([$this->source_tag->id]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => [$this->source_tag->id]]);
		$this->assertNoContent($response);

		$this->assertDatabaseMissing('albums_tags', [
			'album_id' => $this->album1->id,
			'tag_id' => $this->source_tag->id,
		]);
		// album2 belongs to a different (non-acting) user and must remain tagged.
		$this->assertDatabaseHas('albums_tags', [
			'album_id' => $this->album2->id,
			'tag_id' => $this->source_tag->id,
		]);

		// The tag itself must survive since album2 still references it.
		$this->assertNotNull(Tag::find($this->source_tag->id));
	}
}
