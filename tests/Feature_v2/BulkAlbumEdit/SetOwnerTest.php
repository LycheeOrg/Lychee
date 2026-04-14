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
 * Feature tests for POST /api/v2/BulkAlbumEdit::setOwner.
 */
class SetOwnerTest extends BaseApiWithDataTest
{
	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [$this->album1->id],
			'owner_id' => $this->userMayUpload2->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [$this->album1->id],
			'owner_id' => $this->userMayUpload2->id,
		]);
		$this->assertForbidden($response);
	}

	// ── validation ────────────────────────────────────────────────────────────

	public function testEmptyAlbumIdsReturns422(): void
	{
		$response = $this->actingAs($this->admin)->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [],
			'owner_id' => $this->userMayUpload2->id,
		]);
		$this->assertUnprocessable($response);
	}

	public function testNonexistentOwnerIdReturns422(): void
	{
		$response = $this->actingAs($this->admin)->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [$this->album1->id],
			'owner_id' => 9999999,
		]);
		$this->assertUnprocessable($response);
	}

	// ── transfer ─────────────────────────────────────────────────────────────

	public function testOwnershipTransferredAndAlbumMovedToRoot(): void
	{
		$response = $this->actingAs($this->admin)->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [$this->album1->id],
			'owner_id' => $this->userMayUpload2->id,
		]);
		$this->assertNoContent($response);

		$album = Album::find($this->album1->id);
		$this->assertSame($this->userMayUpload2->id, $album->owner_id);
		$this->assertNull($album->parent_id, 'Album should have been moved to root');
	}

	public function testDescendantsOwnershipUpdated(): void
	{
		$response = $this->actingAs($this->admin)->postJson('BulkAlbumEdit::setOwner', [
			'album_ids' => [$this->album1->id],
			'owner_id' => $this->userMayUpload2->id,
		]);
		$this->assertNoContent($response);

		// subAlbum1 is a child of album1 — it should also be transferred
		$subAlbum = Album::find($this->subAlbum1->id);
		$this->assertSame($this->userMayUpload2->id, $subAlbum->owner_id);
	}
}
