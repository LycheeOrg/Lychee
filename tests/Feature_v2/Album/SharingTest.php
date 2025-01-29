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

namespace Tests\Feature_v2\Album;

use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Models\Album;
use Tests\Feature_v2\Base\BaseApiV2Test;

class SharingTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);
	}

	public function testUserGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing::all');
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $this->perm1->id,
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload1->id],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Sharing', ['album_id' => $this->album2->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1);

		$id = $response->json()[0]['id'];
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Sharing', ['perm_id' => $id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload2)->deleteJson('Sharing', ['perm_id' => $id]);
		$this->assertNoContent($response);
	}

	public function testUpdateOverrideForbidden(): void
	{
		$response = $this->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		$response = $this->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload2)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdate(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', []);
		$this->assertUnprocessable($response);

		// Update sub album permission.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);
		self::assertEquals(1, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->first();

		// Update the permission with false
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $perm->id,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
			'grants_upload' => false,
		]);
		$this->assertOk($response);

		// Verify the permission
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->first();
		self::assertFalse($perm->grants_edit);
		self::assertFalse($perm->grants_delete);
		self::assertFalse($perm->grants_download);
		self::assertFalse($perm->grants_full_photo_access);
		self::assertFalse($perm->grants_upload);

		// Apply update again
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);
		// Verify the count is still 1.
		self::assertEquals(1, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());

		// Verify the permission
		$perm = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->first();
		self::assertTrue($perm->grants_edit);
		self::assertTrue($perm->grants_delete);
		self::assertTrue($perm->grants_download);
		self::assertTrue($perm->grants_full_photo_access);
		self::assertTrue($perm->grants_upload);
	}

	public function testOverride(): void
	{
		// Set up the permission in subSlbum
		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userLocked->id],
			'album_ids' => [$this->subAlbum1->id],
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(1, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)->count());

		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userNoUpload->id],
			'album_ids' => [$this->album1->id],
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
		self::assertEquals(2, AccessPermission::where(APC::BASE_ALBUM_ID, '=', $this->album1->id)->count());

		// Update sub album permission.
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);
		self::assertEquals(0,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userLocked->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userMayUpload2->id)
				->count());
		self::assertEquals(1,
			AccessPermission::query()
				->where(APC::BASE_ALBUM_ID, '=', $this->subAlbum1->id)
				->where(APC::USER_ID, '=', $this->userNoUpload->id)
				->count());
	}
}