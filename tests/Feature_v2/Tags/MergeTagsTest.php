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
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class MergeTagsTest extends BaseApiWithDataTest
{
	protected Tag $source_tag;
	protected Tag $destination_tag;

	public function setUp(): void
	{
		parent::setUp();

		// Create two test tags for merging
		$this->source_tag = Tag::create(['name' => 'source_tag_to_merge']);
		$this->destination_tag = Tag::create(['name' => 'destination_tag']);

		// Associate the source tag with a test photo
		DB::table('photos_tags')->insert([
			'photo_id' => $this->photo1->id,
			'tag_id' => $this->source_tag->id,
		]);
	}

	public function testMergeTagsGuest(): void
	{
		$response = $this->putJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->putJson('Tag', ['tag_id' => $this->source_tag->id, 'destination_id' => $this->destination_tag->id]);
		$this->assertUnauthorized($response);
	}

	public function testMergeTagsUserWithoutUploadRight(): void
	{
		$response = $this->actingAs($this->userNoUpload)->putJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userNoUpload)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertForbidden($response);
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
		$this->assertDatabaseHas('photos_tags', [
			'photo_id' => $this->photo1->id,
			'tag_id' => $this->destination_tag->id,
		]);
	}

	public function testMergeTagsWithMissingSourceTagId(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertUnprocessable($response);
	}

	public function testMergeTagsWithMissingDestinationTagId(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
		]);
		$this->assertUnprocessable($response);
	}

	public function testMergeSameTag(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->source_tag->id,
		]);
		$this->assertUnprocessable($response);
	}

	public function testMergeNonExistentTag(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => 999999,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNotFound($response);
	}

	public function testMergeToNonExistentTag(): void
	{
		$response = $this->actingAs($this->admin)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => 999999,
		]);
		$this->assertNotFound($response);
	}

	public function testEditTagAdvanced(): void
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

		$response = $this->actingAs($this->userMayUpload1)->putJson('Tag', [
			'tag_id' => $this->source_tag->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		// With this we validate the photo2 remains under tag `source_tag_to_merge`.
		$this->assertDatabaseCount('photos_tags', 3);
		$this->assertDatabaseMissing('photos_tags', [
			'tag_id' => $this->source_tag->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->tag_test->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->destination_tag->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->source_tag->id,
			'photo_id' => $this->photo2->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->putJson('Tag', [
			'tag_id' => $this->tag_test->id,
			'destination_id' => $this->destination_tag->id,
		]);
		$this->assertNoContent($response);

		// With this we validate the photo2 remains under tag `source_tag_to_merge`.
		$this->assertDatabaseCount('photos_tags', 2);
		$this->assertDatabaseMissing('photos_tags', [
			'tag_id' => $this->tag_test->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->destination_tag->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->source_tag->id,
			'photo_id' => $this->photo2->id,
		]);
		$this->assertDatabaseCount('photos_tags', 2);
		$this->assertDatabaseCount('tags', 2);
	}
}
