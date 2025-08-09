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

class DeleteTagsTest extends BaseApiWithDataTest
{
	public function testDeleteTagGuest(): void
	{
		$response = $this->deleteJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('Tag', ['tags' => [1, 2]]);
		$this->assertUnauthorized($response);
	}

	public function testDeleteTagUserWithoutUploadRight(): void
	{
		$response = $this->actingAs($this->userNoUpload)->deleteJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userNoUpload)->deleteJson('Tag', ['tags' => [1, 2]]);
		$this->assertForbidden($response);
	}

	public function testDeleteTagUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => [$this->tag_test->id]]);
		$this->assertNoContent($response);

		// Validate that the tag is fully deleted
		$this->assertDatabaseCount('photos_tags', 0);
		$this->assertDatabaseCount('tags', 0);
	}

	public function testDeleteInvalidTagFormat(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => 'not_an_array']);
		$this->assertUnprocessable($response);
	}

	public function testDeleteEmptyTagList(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => []]);
		$this->assertUnprocessable($response);
	}

	public function testDeleteNonExistentTag(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => [99999999]]);
		$this->assertNoContent($response);
	}

	public function testDeleteTagAdvanced(): void
	{
		// Tag photo2 by User2 for `test`
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo2->id],
			'tags' => [$this->tag_test->name],
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		// Validate that `test` has 2 photos associated.
		$this->assertDatabaseCount('photos_tags', 2);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Tag', ['tags' => [$this->tag_test->id]]);
		$this->assertNoContent($response);

		// With this we validate the photo2 remains under tag `test` but that for user1, the tag is deleted.
		$this->assertDatabaseCount('photos_tags', 1);
		$this->assertDatabaseMissing('photos_tags', [
			'tag_id' => $this->tag_test->id,
			'photo_id' => $this->photo1->id,
		]);
		$this->assertDatabaseHas('photos_tags', [
			'tag_id' => $this->tag_test->id,
			'photo_id' => $this->photo2->id,
		]);
		$this->assertDatabaseCount('tags', 1);
	}
}
