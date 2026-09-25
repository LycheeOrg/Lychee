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

use App\Constants\PhotoAlbum as PA;
use App\Models\Album;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 072 — `Album::merge` authorization (FR-072-15/16/17/18).
 *
 * `CAN_DELETE` on a non-owned album comes from `grants_delete` on its parent,
 * so these scenarios merge subAlbum1 with delete granted on album1.
 */
class AlbumMergeGrantTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	private Album $victim_other;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
		$this->victim_other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
	}

	private function albumExists(Album $album): bool
	{
		return Album::query()->where('id', '=', $album->id)->exists();
	}

	private function linkedTo(Album $album): array
	{
		return DB::table(PA::PHOTO_ALBUM)->where(PA::ALBUM_ID, '=', $album->id)->pluck(PA::PHOTO_ID)->all();
	}

	/** S-072-11 — GHSA-jp9x-63pp-pv4v merge reproduction. */
	public function testEditOnlyCannotMergeVictimAlbumIntoOwnAlbum(): void
	{
		$this->grant($this->album1, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertForbidden($response);
		self::assertTrue($this->albumExists($this->album1));
		self::assertTrue($this->albumExists($this->subAlbum1));
	}

	/** S-072-12 — move + delete, but crossing owners: refused, no photo link leaks. */
	public function testMoveAndDeleteCannotMergeAcrossOwners(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->grant($this->subAlbum1, ['move']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertTrue($this->albumExists($this->subAlbum1));
		self::assertSame([], $this->linkedTo($this->attacker_album));
	}

	/** S-072-13 — move + delete within the same owner. */
	public function testMoveAndDeleteCanMergeWithinSameOwner(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->grant($this->subAlbum1, ['move']);
		$this->grant($this->victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->victim_other->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		self::assertFalse($this->albumExists($this->subAlbum1));
		self::assertContains($this->subPhoto1->id, $this->linkedTo($this->victim_other));
	}

	/** Merge deletes the source: move without delete is refused (FR-072-15). */
	public function testMoveWithoutDeleteCannotMerge(): void
	{
		$this->grant($this->subAlbum1, ['move']);
		$this->grant($this->victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->victim_other->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
		self::assertTrue($this->albumExists($this->subAlbum1));
	}

	/** Delete without move is refused (FR-072-15). */
	public function testDeleteWithoutMoveCannotMerge(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->grant($this->subAlbum1, ['edit']);
		$this->grant($this->victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->victim_other->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	/** S-072-14 — an owner may merge their own album into a foreign editable album. */
	public function testOwnerCanMergeOwnAlbumIntoForeignEditableAlbum(): void
	{
		$this->grant($this->victim_other, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::merge', [
			'album_id' => $this->victim_other->id,
			'album_ids' => [$this->attacker_album->id],
		]);
		$this->assertNoContent($response);
		self::assertFalse($this->albumExists($this->attacker_album));
	}

	/** S-072-16 — admin bypass. */
	public function testAdminCanMergeAcrossOwners(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Album::merge', [
			'album_id' => $this->attacker_album->id,
			'album_ids' => [$this->album1->id],
		]);
		$this->assertNoContent($response);
	}
}
