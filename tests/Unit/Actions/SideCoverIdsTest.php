<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions;

use App\Actions\Album\StructOfArrays\SideCoverIds;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Feature 075, FR-075-05 / FR-075-09 (S-075-05, S-075-06, S-075-07).
 */
class SideCoverIdsTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private const MAX = ['m1', 'm2', 'm3'];
	private const LEAST = ['l1', 'l2', 'l3'];

	public function setUp(): void
	{
		parent::setUp();
		Configs::set('show_cover_of_locked_albums', '0');
		Configs::set('show_selected_cover_on_locked_albums', '0');
	}

	/**
	 * @param array{0:string|null,1:string|null,2:string|null} $max
	 * @param array{0:string|null,1:string|null,2:string|null} $least
	 */
	private function row(int $owner_id, array $max = self::MAX, array $least = self::LEAST, ?string $password = null, ?string $cover_id = null): object
	{
		return (object) [
			'id' => 'album-id',
			'owner_id' => $owner_id,
			'password' => $password,
			'cover_id' => $cover_id,
			'auto_cover_id_max_privilege' => $max[0],
			'auto_cover_id_max_privilege_2' => $max[1],
			'auto_cover_id_max_privilege_3' => $max[2],
			'auto_cover_id_least_privilege' => $least[0],
			'auto_cover_id_least_privilege_2' => $least[1],
			'auto_cover_id_least_privilege_3' => $least[2],
		];
	}

	// ── S-075-05: setting off / no primary ──────────────────────

	public function testDisabledYieldsNulls(): void
	{
		$owner = User::factory()->create();

		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row($owner->id), 'm1', $owner, [], false));
		self::assertSame([null, null], SideCoverIds::fromCacheRow((object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => 'c'], 'a', false));
	}

	public function testNullPrimaryYieldsNulls(): void
	{
		$owner = User::factory()->create();

		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row($owner->id), null, $owner, [], true));
		self::assertSame([null, null], SideCoverIds::fromCacheRow((object) ['photo_id' => 'a', 'photo_id_2' => 'b', 'photo_id_3' => 'c'], null, true));
	}

	// ── S-075-04: privilege selection ───────────────────────────

	public function testOwnerAndAdminGetMaxPrivilegeTriple(): void
	{
		$owner = User::factory()->create();
		$admin = User::factory()->create(['may_administrate' => true]);

		self::assertSame(['m2', 'm3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'm1', $owner, [], true));
		self::assertSame(['m2', 'm3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'm1', $admin, [], true));
	}

	public function testGuestAndOtherUserGetLeastPrivilegeTriple(): void
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();

		self::assertSame(['l2', 'l3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'l1', null, [], true));
		self::assertSame(['l2', 'l3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'l1', $other, [], true));
	}

	// ── S-075-02 / S-075-06: filtering ──────────────────────────

	public function testNullsAreDroppedAndPadded(): void
	{
		$owner = User::factory()->create();

		self::assertSame(['m2', null], SideCoverIds::forAlbumRow($this->row($owner->id, ['m1', 'm2', null]), 'm1', $owner, [], true));
		self::assertSame([null, null], SideCoverIds::forAlbumRow($this->row($owner->id, ['m1', null, null]), 'm1', $owner, [], true));
	}

	public function testPrimaryIsDroppedWhereverItRanks(): void
	{
		$owner = User::factory()->create();

		// Manual cover equal to rank 1 → ranks 2, 3.
		self::assertSame(['m2', 'm3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'm1', $owner, [], true));
		// Manual cover equal to rank 2 → ranks 1, 3.
		self::assertSame(['m1', 'm3'], SideCoverIds::forAlbumRow($this->row($owner->id), 'm2', $owner, [], true));
		// Manual cover equal to rank 3 → ranks 1, 2.
		self::assertSame(['m1', 'm2'], SideCoverIds::forAlbumRow($this->row($owner->id), 'm3', $owner, [], true));
		// Manual cover outside the triple → ranks 1, 2.
		self::assertSame(['m1', 'm2'], SideCoverIds::forAlbumRow($this->row($owner->id), 'manual', $owner, [], true));
	}

	// ── S-075-07: locked albums ─────────────────────────────────

	public function testLockedAlbumHidesSidesUnlessShowCoverOfLockedAlbums(): void
	{
		$owner = User::factory()->create();
		$row = $this->row($owner->id, password: 'hash', cover_id: 'manual');

		// Primary shown via show_selected_cover_on_locked_albums, sides still hidden.
		Configs::set('show_selected_cover_on_locked_albums', '1');
		self::assertSame([null, null], SideCoverIds::forAlbumRow($row, 'manual', null, [], true));
		self::assertTrue(SideCoverIds::isHiddenByLock($row, []));

		// show_cover_of_locked_albums reveals sides.
		Configs::set('show_cover_of_locked_albums', '1');
		self::assertSame(['l1', 'l2'], SideCoverIds::forAlbumRow($row, 'manual', null, [], true));
		self::assertFalse(SideCoverIds::isHiddenByLock($row, []));
	}

	public function testUnlockedAlbumShowsSides(): void
	{
		$owner = User::factory()->create();
		$row = $this->row($owner->id, password: 'hash');

		self::assertSame(['l2', 'l3'], SideCoverIds::forAlbumRow($row, 'l1', null, ['album-id'], true));
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
