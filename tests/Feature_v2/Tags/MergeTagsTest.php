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
		$this->assertTrue(
			DB::table('photos_tags')
				->where('photo_id', $this->photo1->id)
				->where('tag_id', $this->destination_tag->id)
				->exists()
		);
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
}
