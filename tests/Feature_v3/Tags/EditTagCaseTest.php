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

namespace Tests\Feature_v3\Tags;

use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Case-only renames of a tag through `PATCH /api/v2/Tag` (Feature 083, FR-083-01).
 */
class EditTagCaseTest extends BaseApiWithDataTest
{
	public function testCaseOnlyRenameKeepsTag(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'Test']);
		$this->assertNoContent($response);

		$this->assertDatabaseHas('tags', ['id' => $this->tag_test->id, 'name' => 'Test']);
		$this->assertDatabaseHas('photos_tags', ['tag_id' => $this->tag_test->id, 'photo_id' => $this->photo1->id]);
		$this->assertDatabaseCount('tags', 1);
	}

	public function testCaseOnlyRenameAppliesToOtherOwners(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Photo::tags', [
			'photo_ids' => [$this->photo2->id],
			'tags' => [$this->tag_test->name],
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'TEST']);
		$this->assertNoContent($response);

		$this->assertDatabaseHas('tags', ['id' => $this->tag_test->id, 'name' => 'TEST']);
		$this->assertDatabaseHas('photos_tags', ['tag_id' => $this->tag_test->id, 'photo_id' => $this->photo1->id]);
		$this->assertDatabaseHas('photos_tags', ['tag_id' => $this->tag_test->id, 'photo_id' => $this->photo2->id]);
		$this->assertDatabaseCount('tags', 1);
	}
}
