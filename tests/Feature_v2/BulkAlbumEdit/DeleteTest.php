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

use App\Models\Album;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for DELETE /api/v2/BulkAlbumEdit.
 */
class DeleteTest extends BaseApiWithDataTest
{
	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->deleteJson('BulkAlbumEdit', ['album_ids' => [$this->album1->id]]);
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('BulkAlbumEdit', ['album_ids' => [$this->album1->id]]);
		$this->assertForbidden($response);
	}

	// ── validation ────────────────────────────────────────────────────────────

	public function testEmptyAlbumIdsReturns422(): void
	{
		$response = $this->actingAs($this->admin)->deleteJson('BulkAlbumEdit', ['album_ids' => []]);
		$this->assertUnprocessable($response);
	}

	// ── deletion ─────────────────────────────────────────────────────────────

	public function testAlbumDeletedSuccessfully(): void
	{
		// Create a standalone album owned by admin so we can safely delete it
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();

		$response = $this->actingAs($this->admin)->deleteJson('BulkAlbumEdit', ['album_ids' => [$album->id]]);
		$this->assertNoContent($response);

		$this->assertNull(Album::find($album->id), 'Album should have been deleted');
	}
}
