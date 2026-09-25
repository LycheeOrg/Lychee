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
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 072 — `Album::move` authorization (FR-072-13/14/17/18).
 */
class AlbumMoveGrantTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
	}

	/** S-072-08 — GHSA-jp9x-63pp-pv4v move reproduction. */
	public function testEditOnlyCannotMoveVictimAlbumUnderOwnAlbum(): void
	{
		$this->grant($this->album1, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertForbidden($response);

		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->album1));
		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->subAlbum1));
		self::assertNull($this->parentOf($this->album1));
		$this->assertForbidden($this->actingAs($this->attacker)->getJsonWithData('Album::albums', ['album_id' => $this->subAlbum1->id]));
	}

	/** S-072-09 — cross-owner move needs ownership, not just the move grant. */
	public function testMoveGrantCannotMoveVictimAlbumUnderOwnAlbum(): void
	{
		$this->grant($this->album1, ['edit', 'move', 'delete']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->subAlbum1));
		self::assertSame($this->album1->id, $this->parentOf($this->subAlbum1));
	}

	/** Edit alone no longer allows reorganising (FR-072-13). */
	public function testEditOnlyCannotMoveToRoot(): void
	{
		$this->grant($this->album1, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertSame($this->album1->id, $this->parentOf($this->subAlbum1));
	}

	/** Q-072-09 — the move grant covers content: a grant on the album itself does not let it be moved. */
	public function testMoveGrantOnTheAlbumItselfDoesNotAllowMovingIt(): void
	{
		$this->grant($this->subAlbum1, ['move']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertSame($this->album1->id, $this->parentOf($this->subAlbum1));
	}

	/** Q-072-09 — a root album has no parent: only its owner may move it. */
	public function testMoveGrantCannotMoveARootAlbum(): void
	{
		$victim_other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->grant($this->album1, ['edit', 'move']);
		$this->grant($victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $victim_other->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertForbidden($response);
		self::assertNull($this->parentOf($this->album1));
	}

	/** S-072-10 — move grant on the parent allows moving a child to root, owner unchanged. */
	public function testMoveGrantOnParentCanMoveChildToRoot(): void
	{
		$this->grant($this->album1, ['move']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		self::assertNull($this->parentOf($this->subAlbum1));
		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->subAlbum1));
	}

	/** Same-owner move with move on the source's parent and edit on target (FR-072-17). */
	public function testMoveGrantCanMoveBetweenAlbumsOfSameOwner(): void
	{
		$victim_other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->grant($this->album1, ['move']);
		$this->grant($victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $victim_other->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		self::assertSame($victim_other->id, $this->parentOf($this->subAlbum1));
		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->subAlbum1));
	}

	/** Target still requires edit (FR-072-17). */
	public function testTargetWithoutEditIsRejected(): void
	{
		$victim_other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->grant($this->album1, ['move']);
		$this->grant($victim_other, ['move']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $victim_other->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	/** An owner may still give their own album away (S-072-11 in spec's register: gift). */
	public function testOwnerCanMoveOwnAlbumIntoForeignEditableAlbum(): void
	{
		$this->grant($this->album1, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $this->album1->id,
			'album_ids' => [$this->attacker_album->id],
		]);
		$this->assertNoContent($response);
		self::assertSame($this->userMayUpload1->id, $this->ownerOf($this->attacker_album));
	}

	/** Batch is all-or-nothing (FR-072-18). */
	public function testBatchWithOneForbiddenSourceMovesNothing(): void
	{
		$own_other = Album::factory()->as_root()->owned_by($this->attacker)->create();
		$this->grant($this->album1, ['edit', 'move']);

		$response = $this->actingAs($this->attacker)->postJson('Album::move', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$own_other->id, $this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertNull($this->parentOf($own_other));
	}

	/** S-072-16 — admin bypass. */
	public function testAdminCanMoveAcrossOwners(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Album::move', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertNoContent($response);
	}
}
