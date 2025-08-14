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

use App\Models\Tag;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class TagAlbumsImpactTest extends BaseApiWithDataTest
{
	protected Tag $source_tag;
	protected Tag $destination_tag;
	protected TagAlbum $tagAlbum2;

	public function setUp(): void
	{
		parent::setUp();

		// Create two test tags for merging
		$this->source_tag = Tag::create(['name' => 'source_tag_to_merge']);
		$this->destination_tag = Tag::create(['name' => 'destination_tag']);
		$this->tagAlbum2 = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$this->source_tag, $this->destination_tag])->create();

		// Associate the source tag with a test photo
		DB::table('photos_tags')->insert([
			'photo_id' => $this->photo1->id,
			'tag_id' => $this->source_tag->id,
		]);
	}

	public function testMergeTagsUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		// Check that source tag no longer exists
		$this->assertNull(Tag::find($this->source_tag->id));

		// Check that destination tag now has the photo
		$this->assertDatabaseHas('tag_albums_tags', [
			'album_id' => $this->tagAlbum2->id,
			'tag_id' => $this->destination_tag->id,
		]);
		$this->assertDatabaseMissing('tag_albums_tags', [
			'album_id' => $this->tagAlbum2->id,
			'tag_id' => $this->source_tag->id,
		]);
	}

	public function tesMergeTagAdvanced(): void
	{
		// Tag photo2 by User2 for `test`
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo2->id],
			'tags' => [$this->source_tag->name],
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		$this->assertDatabaseCount('photos_tags', 3);
		// test -> photo1
		// source_tag_to_merge -> photo1
		// source_tag_to_merge -> photo2

		// Validate that `tag_album1` has 1 tag (test).
		// Validate that `tag_album2` has 2 tags (source_tag_to_merge, destination_tag).
		$this->assertDatabaseCount('tag_albums_tags', 3);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		// Validate that `tag_album1` has 1 tag (test).
		// Validate that `tag_album2` has 1 tags (destination_tag).
		$this->assertDatabaseCount('tag_albums_tags', 2);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->tag_test->id,
			'album_id' => $this->tagAlbum1->id,
		]);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->destination_tag->id,
			'album_id' => $this->tagAlbum2->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Tag', [
			'tag_id' => $this->tag_test->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);
		// Validate that `tag_album1` has 1 tag (destination_tag).
		// Validate that `tag_album2` has 1 tags (destination_tag).
		$this->assertDatabaseCount('tag_albums_tags', 2);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->destination_tag->id,
			'album_id' => $this->tagAlbum1->id,
		]);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->destination_tag->id,
			'album_id' => $this->tagAlbum2->id,
		]);
	}

	/**
	 * In this test we validate that when user edits a tag, its associated tag albums are updated accordingly.
	 */
	public function testEditTagAdvanced(): void
	{
		// Tag photo2 by User2 for `test`
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo2->id],
			'tags' => [$this->tag_test->name],
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		// Validate that `test` has 2 photos associated + source_tag_to_merge
		$this->assertDatabaseCount('photos_tags', 3);

		// Validate that `tag_album1` has 1 tag (test).
		// Validate that `tag_album2` has 1 tags (source_tag_to_merge, destination_tag).
		$this->assertDatabaseCount('tag_albums_tags', 3);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'test_edited']);
		$this->assertNoContent($response);

		// test, test_edited, source_tag_to_merge
		// destination_tag is not tied to any photo, so it was removed.
		$this->assertDatabaseCount('tags', 3);

		// With this we validate the tag album1 points to the updated tag `test_edited` (by considering the fact that it does not point to `test` anymore).
		$this->assertDatabaseCount('tag_albums_tags', 2);
		$this->assertDatabaseMissing('tag_albums_tags', [
			'tag_id' => $this->tag_test->id,
			'album_id' => $this->tagAlbum1->id,
		]);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->source_tag->id,
			'album_id' => $this->tagAlbum2->id,
		]);
		$this->assertDatabaseMissing('tag_albums_tags', [ // Was removed by the clean up
			'tag_id' => $this->destination_tag->id,
			'album_id' => $this->tagAlbum1->id,
		]);
	}

	/**
	 * In this test we validate that when a user2 edits their tag, the tag albums of user1 are not impacted.
	 */
	public function testEditTagAdvanced2(): void
	{
		// Tag photo2 by User2 for `test`
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo2->id],
			'tags' => [$this->tag_test->name],
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		// Validate that `test` has 2 photos associated + source_tag_to_merge
		$this->assertDatabaseCount('photos_tags', 3);

		// Validate that `tag_album1` has 1 tag (test).
		// Validate that `tag_album2` has 1 tags (source_tag_to_merge, destination_tag).
		$this->assertDatabaseCount('tag_albums_tags', 3);

		$response = $this->actingAs($this->userMayUpload2)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'test_edited']);
		$this->assertNoContent($response);

		// test, test_edited, source_tag_to_merge
		// destination_tag is not tied to any photo, so it was removed.
		$this->assertDatabaseCount('tags', 3);

		// With this we validate the photo2 remains under tag `test`.
		$this->assertDatabaseCount('tag_albums_tags', 2);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->tag_test->id,
			'album_id' => $this->tagAlbum1->id,
		]);
		$this->assertDatabaseHas('tag_albums_tags', [
			'tag_id' => $this->source_tag->id,
			'album_id' => $this->tagAlbum2->id,
		]);
		$this->assertDatabaseMissing('tag_albums_tags', [ // Was removed by the clean up
			'tag_id' => $this->destination_tag->id,
			'album_id' => $this->tagAlbum1->id,
		]);
	}
}
