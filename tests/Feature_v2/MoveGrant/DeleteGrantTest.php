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

namespace Tests\Feature_v2\MoveGrant;

use App\Models\Album;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 072 — the delete grant covers an album's content (Q-072-11, FR-072-41/42).
 *
 * album1 (A) contains photo1 (photo A) and subAlbum1 (AA, containing subPhoto1).
 */
class DeleteGrantTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
	}

	private function deleteAlbum(Album $album): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($this->attacker)->deleteJson('Album', ['album_ids' => [$album->id]]);
	}

	private function deletePhoto(Photo $photo, Album $from): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($this->attacker)->deleteJson('Photo', ['photo_ids' => [$photo->id], 'from_id' => $from->id]);
	}

	private function albumExists(Album $album): bool
	{
		return Album::query()->where('id', '=', $album->id)->exists();
	}

	public function testDeleteOnParentAllowsDeletingSubAlbum(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->assertNoContent($this->deleteAlbum($this->subAlbum1));
		self::assertFalse($this->albumExists($this->subAlbum1));
	}

	public function testDeleteOnAlbumDoesNotAllowDeletingIt(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->assertForbidden($this->deleteAlbum($this->album1));
		self::assertTrue($this->albumExists($this->album1));
	}

	public function testDeleteOnSubAlbumItselfDoesNotAllowDeletingIt(): void
	{
		$this->grant($this->subAlbum1, ['delete']);
		$this->assertForbidden($this->deleteAlbum($this->subAlbum1));
		self::assertTrue($this->albumExists($this->subAlbum1));
	}

	public function testEditOnParentDoesNotAllowDeletingSubAlbum(): void
	{
		$this->grant($this->album1, ['edit', 'move']);
		$this->assertForbidden($this->deleteAlbum($this->subAlbum1));
	}

	public function testOwnerCanDeleteOwnRootAlbum(): void
	{
		$this->assertNoContent($this->deleteAlbum($this->attacker_album));
	}

	public function testBatchWithOneForbiddenAlbumDeletesNothing(): void
	{
		$this->grant($this->album1, ['delete']);
		$response = $this->actingAs($this->attacker)->deleteJson('Album', ['album_ids' => [$this->subAlbum1->id, $this->album1->id]]);
		$this->assertForbidden($response);
		self::assertTrue($this->albumExists($this->subAlbum1));
	}

	public function testDeleteOnAlbumAllowsDeletingItsPhotos(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->assertNoContent($this->deletePhoto($this->photo1, $this->album1));
	}

	public function testDeleteOnParentDoesNotAllowDeletingPhotosOfSubAlbum(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->assertForbidden($this->deletePhoto($this->subPhoto1, $this->subAlbum1));
	}
}
