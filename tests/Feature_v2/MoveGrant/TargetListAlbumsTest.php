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
 * `Album::getTargetListAlbums` only lists editable albums.
 */
class TargetListAlbumsTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	private Album $victim_editable;
	private Album $victim_readonly;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
		$this->victim_editable = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->victim_readonly = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->grant($this->victim_editable, ['edit']);
		$this->grant($this->victim_readonly, []);
	}

	/**
	 * @return string[]
	 */
	private function targetIds(?Album $source): array
	{
		$uri = 'Album::getTargetListAlbums' . ($source !== null ? '?album_ids[]=' . $source->id : '');
		$response = $this->actingAs($this->attacker)->getJson($uri);
		$this->assertOk($response);

		return array_values(array_filter(array_column($response->json(), 'id')));
	}

	/** Photo pickers call without sources. */
	public function testOnlyEditableAlbumsAreListed(): void
	{
		$ids = $this->targetIds(null);
		self::assertContains($this->victim_editable->id, $ids);
		self::assertContains($this->attacker_album->id, $ids);
		self::assertNotContains($this->victim_readonly->id, $ids);
	}

	/** Move grant on the source's parent opens the album move picker. */
	public function testMoveGrantOnParentOpensThePicker(): void
	{
		$this->grant($this->album1, ['move']);
		$ids = $this->targetIds($this->subAlbum1);
		self::assertContains($this->victim_editable->id, $ids);
		self::assertNotContains($this->subAlbum1->id, $ids);
		self::assertNotContains($this->victim_readonly->id, $ids);
	}

	public function testEditWithoutMoveOnParentCannotOpenThePicker(): void
	{
		$this->grant($this->album1, ['edit']);
		$response = $this->actingAs($this->attacker)->getJson('Album::getTargetListAlbums?album_ids[]=' . $this->subAlbum1->id);
		$this->assertForbidden($response);
	}

	public function testMoveGrantOnTheAlbumItselfCannotOpenThePicker(): void
	{
		$this->grant($this->subAlbum1, ['move']);
		$response = $this->actingAs($this->attacker)->getJson('Album::getTargetListAlbums?album_ids[]=' . $this->subAlbum1->id);
		$this->assertForbidden($response);
	}
}
