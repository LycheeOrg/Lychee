<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions;

use App\Actions\Album\StructOfArrays\SideCoverIds;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Feature 075, FR-075-05 / FR-075-09 (S-075-05, S-075-06, S-075-07),
 * on the viewer-resolved row of Feature 076 (FR-076-06).
 */
class SideCoverIdsTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private const COVERS = ['m1', 'm2', 'm3'];

	public function setUp(): void
	{
		parent::setUp();
		Configs::set('show_cover_of_locked_albums', '0');
		Configs::set('show_selected_cover_on_locked_albums', '0');
	}

	/**
	 * A regular-album listing row after {@see \App\Actions\Album\StructOfArrays\JoinAutoCover}
	 * joined the viewer's automatic cover row (Feature 076).
	 *
	 * @param array{0:string|null,1:string|null,2:string|null} $covers
	 */
	private function row(array $covers = self::COVERS, ?string $password = null, ?string $cover_id = null): object
	{
		return (object) [
			'id' => 'album-id',
			'password' => $password,
			'cover_id' => $cover_id,
			'auto_cover_id' => $covers[0],
			'auto_cover_id_2' => $covers[1],
			'auto_cover_id_3' => $covers[2],
		];
	}

	// ── S-075-05: setting off / no primary ──────────────────────

	public function testDisabledYieldsNulls(): void
	{
		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row(), 'm1', [], false));
		self::assertSame([null, null], SideCoverIds::fromCacheRow((object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => 'c'], 'a', false));
	}

	public function testNullPrimaryYieldsNulls(): void
	{
		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row(), null, [], true));
		self::assertSame([null, null], SideCoverIds::fromCacheRow((object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => 'c'], null, true));
	}

	// ── S-075-02 / S-075-06: filtering ──────────────────────────

	public function testNullsAreDroppedAndPadded(): void
	{
		self::assertSame(['m2', null], SideCoverIds::forAlbumRow($this->row(['m1', 'm2', null]), 'm1', [], true));
		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row(['m1', null, null]), 'm1', [], true));
	}

	public function testPrimaryIsDroppedWhereverItRanks(): void
	{
		// Manual cover equal to rank 1 → ranks 2, 3.
		self::assertSame(['m2', 'm3'], SideCoverIds::forAlbumRow($this->row(), 'm1', [], true));
		// Manual cover equal to rank 2 → ranks 1, 3.
		self::assertSame(['m1', 'm3'], SideCoverIds::forAlbumRow($this->row(), 'm2', [], true));
		// Manual cover equal to rank 3 → ranks 1, 2.
		self::assertSame(['m1', 'm2'], SideCoverIds::forAlbumRow($this->row(), 'm3', [], true));
		// Manual cover outside the triple → ranks 1, 2.
		self::assertSame(['m1', 'm2'], SideCoverIds::forAlbumRow($this->row(), 'manual', [], true));
	}

	// ── S-075-07: locked albums ─────────────────────────────────

	public function testLockedAlbumHidesSidesUnlessShowCoverOfLockedAlbums(): void
	{
		$row = $this->row(password: 'hash', cover_id: 'manual');

		// Primary shown via show_selected_cover_on_locked_albums, sides still hidden.
		Configs::set('show_selected_cover_on_locked_albums', '1');
		self::assertSame([null, null], SideCoverIds::forAlbumRow($row, 'manual', [], true));
		self::assertTrue(SideCoverIds::isHiddenByLock($row, []));

		// show_cover_of_locked_albums reveals sides.
		Configs::set('show_cover_of_locked_albums', '1');
		self::assertSame(['m1', 'm2'], SideCoverIds::forAlbumRow($row, 'manual', [], true));
		self::assertFalse(SideCoverIds::isHiddenByLock($row, []));
	}

	public function testUnlockedAlbumShowsSides(): void
	{
		$row = $this->row(password: 'hash');

		self::assertSame(['m2', 'm3'], SideCoverIds::forAlbumRow($row, 'm1', ['album-id'], true));
		self::assertFalse(SideCoverIds::isHiddenByLock($row, ['album-id']));
	}

	// ── FR-075-09: cache rows ───────────────────────────────────

	public function testCacheRowSidesExcludePrimaryAndNulls(): void
	{
		$row = (object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => 'c'];

		self::assertSame(['b', 'c'], SideCoverIds::fromCacheRow($row, 'a', true));
		// Tag album with a manual cover that happens to be rank 2.
		self::assertSame(['a', 'c'], SideCoverIds::fromCacheRow($row, 'b', true));
		self::assertSame(['b', null], SideCoverIds::fromCacheRow((object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => null], 'a', true));
	}

	public function testMissingCacheRowYieldsNulls(): void
	{
		self::assertSame([null, null], SideCoverIds::fromCacheRow(null, 'a', true));
	}
}
