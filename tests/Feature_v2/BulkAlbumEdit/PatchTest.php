<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\BulkAlbumEdit;

use App\Models\AccessPermission;
use App\Models\Album;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for PATCH /api/v2/BulkAlbumEdit.
 */
class PatchTest extends BaseApiWithDataTest
{
	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->patchJson('BulkAlbumEdit', ['album_ids' => [$this->album1->id], 'is_nsfw' => true]);
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('BulkAlbumEdit', ['album_ids' => [$this->album1->id], 'is_nsfw' => true]);
		$this->assertForbidden($response);
	}

	// ── validation ────────────────────────────────────────────────────────────

	public function testEmptyAlbumIdsReturns422(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', ['album_ids' => [], 'is_nsfw' => true]);
		$this->assertUnprocessable($response);
	}

	public function testNoOptionalFieldsReturns422(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', ['album_ids' => [$this->album1->id]]);
		$this->assertUnprocessable($response);
	}

	public function testInvalidEnumValueReturns422(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'photo_layout' => 'invalid_layout',
		]);
		$this->assertUnprocessable($response);
	}

	// ── base_albums fields ────────────────────────────────────────────────────

	public function testPatchDescriptionUpdatesBaseAlbum(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'description' => 'New description from bulk edit',
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertSame('New description from bulk edit', $this->album1->description);
	}

	public function testPatchCopyrightUpdatesBaseAlbum(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'copyright' => '© 2026 Test Corp',
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertSame('© 2026 Test Corp', $this->album1->copyright);
	}

	public function testPatchIsNsfwUpdatesBaseAlbum(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'is_nsfw' => true,
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertTrue($this->album1->is_nsfw);
	}

	// ── albums columns ────────────────────────────────────────────────────────

	public function testPatchLicenseUpdatesAlbum(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'license' => 'CC0',
		]);
		$this->assertNoContent($response);

		$album = Album::find($this->album1->id);
		$this->assertSame('CC0', $album->license->value);
	}

	// ── visibility fields ─────────────────────────────────────────────────────

	public function testMakeAlbumPublicCreatesAccessPermission(): void
	{
		// Ensure no public perm initially
		AccessPermission::query()
			->where('base_album_id', $this->album1->id)
			->whereNull('user_id')
			->whereNull('user_group_id')
			->delete();

		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'is_public' => true,
		]);
		$this->assertNoContent($response);

		$perm = AccessPermission::query()
			->where('base_album_id', $this->album1->id)
			->whereNull('user_id')
			->whereNull('user_group_id')
			->first();
		$this->assertNotNull($perm, 'A public access_permissions record should have been created');
	}

	public function testMakeAlbumPrivateRemovesAccessPermission(): void
	{
		// Create a public perm first
		$perm = new AccessPermission();
		$perm->base_album_id = $this->album1->id;
		$perm->user_id = null;
		$perm->user_group_id = null;
		$perm->is_link_required = false;
		$perm->grants_full_photo_access = false;
		$perm->grants_download = false;
		$perm->grants_upload = false;
		$perm->save();

		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'is_public' => false,
		]);
		$this->assertNoContent($response);

		$remaining = AccessPermission::query()
			->where('base_album_id', $this->album1->id)
			->whereNull('user_id')
			->whereNull('user_group_id')
			->count();
		$this->assertSame(0, $remaining, 'Public access_permissions record should have been removed');
	}

	// ── inline single-album edit ──────────────────────────────────────────────

	public function testSingleAlbumEditWorksLikeBulk(): void
	{
		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'description' => 'Inline single-album edit',
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertSame('Inline single-album edit', $this->album1->description);
	}

	// ── only provided fields updated ──────────────────────────────────────────

	public function testOnlyProvidedFieldIsUpdated(): void
	{
		// Set a known copyright first
		$this->album1->copyright = 'original';
		$this->album1->save();

		$originalCopyright = $this->album1->copyright;

		$response = $this->actingAs($this->admin)->patchJson('BulkAlbumEdit', [
			'album_ids' => [$this->album1->id],
			'description' => 'only description changed',
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertSame('only description changed', $this->album1->description);
		$this->assertSame($originalCopyright, $this->album1->copyright, 'copyright should not have changed');
	}
}
