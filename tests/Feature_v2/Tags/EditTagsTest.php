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

class EditTagsTest extends BaseApiWithDataTest
{
	public function testEditTagGuest(): void
	{
		$response = $this->patchJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'edited_tag']);
		$this->assertUnauthorized($response);
	}

	public function testEditTagUserWithoutUploadRight(): void
	{
		$response = $this->actingAs($this->userNoUpload)->patchJson('Tag');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => 'edited_tag']);
		$this->assertForbidden($response);
	}

	public function testEditTagUserWithUploadRight(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag');
		$this->assertUnprocessable($response);

		$tag_id = $this->tag_test->id;
		$new_name = 'edited_tag';

		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => $tag_id, 'name' => $new_name]);
		$this->assertNoContent($response);

		// Verify the tag name was changed
		$this->tag_test->refresh();
		$this->assertEquals($new_name, $this->tag_test->name);
	}

	public function testEditTagWithMissingTagId(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['name' => 'edited_tag']);
		$this->assertUnprocessable($response);
	}

	public function testEditTagWithMissingName(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => $this->tag_test->id]);
		$this->assertUnprocessable($response);
	}

	public function testEditNonExistentTag(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => '999999', 'name' => 'edited_tag']);
		$this->assertNotFound($response);
	}

	public function testEditTagWithEmptyName(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => '']);
		$this->assertUnprocessable($response);
	}

	public function testEditTagWithSameName(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('Tag', ['tag_id' => $this->tag_test->id, 'name' => $this->tag_test->name]);
		$this->assertUnprocessable($response);
	}
}
