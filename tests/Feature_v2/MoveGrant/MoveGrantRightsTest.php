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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * v2 `AlbumRightsResource::can_move`/`can_move_content`/`can_merge`.
 *
 * Album head rights describe subAlbum1; `CAN_DELETE` on it comes from
 * `grants_delete` on its parent album1.
 */
class MoveGrantRightsTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
	}

	private function rights(): array
	{
		$response = $this->actingAs($this->attacker)->getJsonWithData('Album::head', ['album_id' => $this->subAlbum1->id]);
		$this->assertOk($response);

		return $response->json('resource.rights');
	}

	public function testEditOnlyHasNoMoveRight(): void
	{
		$this->grant($this->album1, ['edit']);
		$this->grant($this->subAlbum1, ['edit']);
		$rights = $this->rights();
		self::assertFalse($rights['can_move']);
		self::assertFalse($rights['can_move_content']);
		self::assertFalse($rights['can_merge']);
	}

	/** Move on the parent: the album itself may be moved, not its content. */
	public function testMoveOnParentAllowsMovingTheAlbum(): void
	{
		$this->grant($this->album1, ['move']);
		$this->grant($this->subAlbum1, []);
		$rights = $this->rights();
		self::assertTrue($rights['can_move']);
		self::assertFalse($rights['can_move_content']);
		self::assertFalse($rights['can_merge']);
	}

	/** Move on the album: its content may be moved out, the album itself not. */
	public function testMoveOnAlbumAllowsMovingItsContent(): void
	{
		$this->grant($this->subAlbum1, ['move']);
		$rights = $this->rights();
		self::assertFalse($rights['can_move']);
		self::assertTrue($rights['can_move_content']);
		self::assertFalse($rights['can_merge']);
	}

	/** Merge: content leaves the album (move on it) and it is deleted (delete on its parent). */
	public function testMoveOnAlbumAndDeleteOnParentAllowsMerge(): void
	{
		$this->grant($this->album1, ['delete']);
		$this->grant($this->subAlbum1, ['move']);
		$rights = $this->rights();
		self::assertTrue($rights['can_delete']);
		self::assertTrue($rights['can_move_content']);
		self::assertTrue($rights['can_merge']);
	}
}
