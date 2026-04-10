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

namespace Tests\Feature_v2\TrustLevel;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for upload-trust-level visibility filtering.
 *
 * album4 is publicly visible (has perm4 with is_public=true).
 * photo4 belongs to album4 and is owned by userLocked.
 *
 * A photo with is_upload_validated = false must NOT be visible to
 * unauthenticated users or users who are not the owner/admin.
 * The owner and admin must still be able to see it.
 */
class VisibilityFilterTest extends BaseApiWithDataTest
{
	public function testUnvalidatedPhotoIsHiddenFromGuest(): void
	{
		// album4 is already public (perm4); set photo4 as unvalidated
		$this->photo4->is_upload_validated = false;
		$this->photo4->save();

		try {
			// Guest should not see the unvalidated photo
			$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ids = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertNotContains($this->photo4->id, $ids);
		} finally {
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		}
	}

	public function testUnvalidatedPhotoIsVisibleToOwner(): void
	{
		$this->photo4->is_upload_validated = false;
		$this->photo4->save();

		try {
			// The owner (userLocked) should still see it
			$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ids = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertContains($this->photo4->id, $ids);
		} finally {
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		}
	}

	public function testUnvalidatedPhotoIsVisibleToAdmin(): void
	{
		$this->photo4->is_upload_validated = false;
		$this->photo4->save();

		try {
			// Admin should see the unvalidated photo
			$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ids = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertContains($this->photo4->id, $ids);
		} finally {
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		}
	}

	public function testUnvalidatedPhotoIsHiddenFromOtherUser(): void
	{
		$this->photo4->is_upload_validated = false;
		$this->photo4->save();

		try {
			// Another authenticated user (not the owner, not admin) should not see it
			$response = $this->actingAs($this->userNoUpload)->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ids = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertNotContains($this->photo4->id, $ids);
		} finally {
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		}
	}

	public function testExistingPhotosAreValidatedByDefault(): void
	{
		// photo4 was created via factory (trusted by default factory)
		$this->assertTrue($this->photo4->is_upload_validated);
	}
}
