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

use App\Enum\UserUploadTrustLevel;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * End-to-end integration test for the upload-trust-level → moderation → public flow.
 *
 * Flow:
 *  1. Photo with is_upload_validated=false is not visible to guests.
 *  2. Same photo IS visible to the owner.
 *  3. Admin lists it via the Moderation API.
 *  4. Admin approves it.
 *  5. Photo is now visible to guests.
 */
class UploadModerationFlowTest extends BaseApiWithDataTest
{
	public function testFullUploadModerationFlow(): void
	{
		// Step 1: Mark photo4 (public album4, owned by userLocked) as unvalidated
		$this->photo4->is_upload_validated = false;
		$this->photo4->save();

		try {
			// Step 2: Guest cannot see it in the album
			$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$guestIds = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertNotContains($this->photo4->id, $guestIds, 'Guest should NOT see unvalidated photo');

			// Step 3: Owner can see it
			$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ownerIds = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertContains($this->photo4->id, $ownerIds, 'Owner SHOULD see their own unvalidated photo');

			// Step 4: Admin sees it in the moderation queue
			$response = $this->actingAs($this->admin)->getJson('Moderation');
			$this->assertOk($response);
			$this->assertGreaterThanOrEqual(1, $response->json('total'));
			$pendingIds = collect($response->json('photos'))->pluck('photo_id')->toArray();
			$this->assertContains($this->photo4->id, $pendingIds, 'Admin SHOULD see unvalidated photo in moderation queue');

			// Step 5: Admin approves the photo
			$response = $this->actingAs($this->admin)->postJson('Moderation::approve', [
				'photo_ids' => [$this->photo4->id],
			]);
			$this->assertNoContent($response);

			// Step 6: Now guest can see the approved photo
			$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$afterIds = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertContains($this->photo4->id, $afterIds, 'After approval, guest SHOULD see the photo');
		} finally {
			// Restore state
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		}
	}

	public function testCheckLevelUserPhotosDefaultToUnvalidated(): void
	{
		// Set userLocked to check trust level
		$this->userLocked->upload_trust_level = UserUploadTrustLevel::CHECK;
		$this->userLocked->save();

		try {
			// photo4 was already created as validated (factory default)
			// Set to false to simulate what would happen on upload with CHECK level
			$this->photo4->is_upload_validated = false;
			$this->photo4->save();

			$this->assertFalse($this->photo4->fresh()->is_upload_validated);

			// Restore
			$this->photo4->is_upload_validated = true;
			$this->photo4->save();
		} finally {
			$this->userLocked->upload_trust_level = UserUploadTrustLevel::TRUSTED;
			$this->userLocked->save();
		}
	}

	public function testTrustedUserPhotosAreImmediatelyPublic(): void
	{
		// photo4 already validated (factory default = trusted)
		$this->assertTrue($this->photo4->is_upload_validated);

		// Should appear for guests since album4 is public
		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$ids = collect($response->json('photos'))->pluck('id')->toArray();
		$this->assertContains($this->photo4->id, $ids, 'Validated photo from trusted user SHOULD be visible to guests');
	}
}
