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

namespace Tests\Feature_v2\Photo;

use App\Models\Album;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class GetPhotoAlbumsTest extends BaseApiWithDataTest
{
	/**
	 * S-018-01: Owner sees all albums for their photo.
	 */
	public function testOwnerSeesAllAlbums(): void
	{
		// photo1 is in album1. Add it to subAlbum1 as well.
		$this->photo1->albums()->syncWithoutDetaching([$this->subAlbum1->id]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Photo/' . $this->photo1->id . '/albums');
		$this->assertOk($response);

		$response->assertJsonCount(2);
		$response->assertJsonFragment(['id' => $this->album1->id, 'title' => $this->album1->title]);
		$response->assertJsonFragment(['id' => $this->subAlbum1->id, 'title' => $this->subAlbum1->title]);
	}

	/**
	 * S-018-02, S-018-03: Shared user sees only accessible albums, inaccessible ones filtered.
	 */
	public function testSharedUserSeesAccessibleAlbumsOnly(): void
	{
		// photo1 is in album1 (shared with userMayUpload2 via perm1).
		// Also place photo1 in album3 (owned by userNoUpload, NOT shared with userMayUpload2).
		$this->photo1->albums()->syncWithoutDetaching([$this->album3->id]);

		// userMayUpload2 can access album1 (via perm1) but NOT album3
		$response = $this->actingAs($this->userMayUpload2)->getJson('Photo/' . $this->photo1->id . '/albums');
		$this->assertOk($response);

		$response->assertJsonCount(1);
		$response->assertJsonFragment(['id' => $this->album1->id, 'title' => $this->album1->title]);
		$response->assertJsonMissing(['id' => $this->album3->id]);
	}

	/**
	 * S-018-04: Guest sees public albums only.
	 */
	public function testGuestSeesPublicAlbumsOnly(): void
	{
		// photo4 is in album4 (public via perm4). Also place it in album1 (private).
		$this->photo4->albums()->syncWithoutDetaching([$this->album1->id]);

		$response = $this->getJson('Photo/' . $this->photo4->id . '/albums');
		$this->assertOk($response);

		$response->assertJsonCount(1);
		$response->assertJsonFragment(['id' => $this->album4->id, 'title' => $this->album4->title]);
		$response->assertJsonMissing(['id' => $this->album1->id]);
	}

	/**
	 * S-018-05: Guest denied for private photo.
	 */
	public function testGuestDeniedForPrivatePhoto(): void
	{
		$response = $this->getJson('Photo/' . $this->photo1->id . '/albums');
		$this->assertUnauthorized($response);
	}

	/**
	 * S-018-06: Non-existent photo returns 404.
	 */
	public function testNonExistentPhotoReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Photo/AAAAAAAAAAAAAAAAAAAAAAAA/albums');
		$this->assertNotFound($response);
	}

	/**
	 * S-018-07: Photo with no albums returns empty array.
	 */
	public function testPhotoWithNoAlbumsReturnsEmptyArray(): void
	{
		// photoUnsorted has no album associations
		$response = $this->actingAs($this->userMayUpload1)->getJson('Photo/' . $this->photoUnsorted->id . '/albums');
		$this->assertOk($response);

		$response->assertJsonCount(0);
		$response->assertExactJson([]);
	}
}
