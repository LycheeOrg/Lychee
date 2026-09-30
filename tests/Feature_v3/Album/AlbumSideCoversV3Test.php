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

namespace Tests\Feature_v3\Album;

use App\Events\AlbumListingCacheFlushRequested;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 075 – Album Hover Side Covers.
 *
 * Covers FR-075-04..07 and S-075-04..07, S-075-16, S-075-18 for the
 * regular-album listings (`/Albums/{id}/children`, `/Albums/root`,
 * `/Albums/pinned`, `/Search/albums`).
 */
class AlbumSideCoversV3Test extends BaseApiWithDataTest
{
	/** Query count of `GET /Albums/{id}` for a 1-child parent, measured before Feature 075 (NFR-075-01). */
	private const CHILDREN_QUERY_BASELINE = 21;

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		Configs::set('sorting_photos_col', 'created_at');
		Configs::set('sorting_photos_order', 'ASC');
		Configs::set('show_cover_of_locked_albums', '0');
		Configs::set('show_selected_cover_on_locked_albums', '0');
	}

	/**
	 * @return array<int,string> photo ids in created_at order
	 */
	private function addPhotos(Album $album, User $owner, int $count, int $day_offset = 0): array
	{
		$ids = [];
		for ($i = 0; $i < $count; $i++) {
			$ids[] = Photo::factory()->in($album)->owned_by($owner)->create([
				'created_at' => Carbon::parse('2024-01-01 10:00:00')->addDays($day_offset + $i),
			])->id;
		}

		return $ids;
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	/**
	 * @return array{0:int,1:array<string,mixed>}
	 */
	private function rowOf(array $json, string $album_id): array
	{
		$idx = array_search($album_id, $json['ids'], true);
		self::assertNotFalse($idx, 'album ' . $album_id . ' missing from listing');

		return [$idx, $json];
	}

	// ── FR-075-04 / S-075-18 ─────────────────────────────────────

	public function testSettingChangeFlushesListingCache(): void
	{
		Event::fake([AlbumListingCacheFlushRequested::class]);

		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				['key' => 'album_hover_side_covers_enabled', 'value' => '0'],
			],
		])->assertOk();

		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

	// ── S-075-04: privilege selection per viewer ─────────────────

	public function testOwnerGetsMaxPrivilegeSidesAndGuestGetsLeastPrivilegeSides(): void
	{
		$owner = User::factory()->create();
		$root = Album::factory()->as_root()->owned_by($owner)->create();
		$public_child = Album::factory()->children_of($root)->owned_by($owner)->create();
		$private_child = Album::factory()->children_of($root)->owned_by($owner)->create();
		AccessPermission::factory()->public()->visible()->for_album($root)->create();
		AccessPermission::factory()->public()->visible()->for_album($public_child)->create();
		$public_ids = $this->addPhotos($public_child, $owner, 3, 10);
		$private_ids = $this->addPhotos($private_child, $owner, 3, 0);
		$this->recompute($root);

		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3('Albums/root?scope=own')->assertOk()->json(), $root->id);
		// Owner: max-privilege ranks are the three earliest photos, all private.
		self::assertSame($private_ids[0], $json['cover_ids'][$i]);
		self::assertSame($private_ids[1], $json['cover_ids_2'][$i]);
		self::assertSame($private_ids[2], $json['cover_ids_3'][$i]);

		\Illuminate\Support\Facades\Auth::logout();
		[$i, $json] = $this->rowOf($this->getJsonV3('Albums/root?scope=shared')->assertOk()->json(), $root->id);
		// Guest: least-privilege ranks are the public photos only.
		self::assertSame($public_ids[0], $json['cover_ids'][$i]);
		self::assertSame($public_ids[1], $json['cover_ids_2'][$i]);
		self::assertSame($public_ids[2], $json['cover_ids_3'][$i]);
	}

	// ── S-075-05: setting off ────────────────────────────────────

	public function testSettingOffSendsNullSidesButKeepsCover(): void
	{
		Configs::set('album_hover_side_covers_enabled', '0');
		$owner = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($owner)->create();
		$child = Album::factory()->children_of($parent)->owned_by($owner)->create();
		$ids = $this->addPhotos($child, $owner, 3);
		$this->recompute($child);

		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3("Albums/{$parent->id}")->assertOk()->json(), $child->id);
		self::assertSame($ids[0], $json['cover_ids'][$i]);
		self::assertNull($json['cover_ids_2'][$i]);
		self::assertNull($json['cover_ids_3'][$i]);
	}

	// ── S-075-06: manual cover never repeats in the fan ──────────

	public function testManualCoverIsExcludedFromSides(): void
	{
		$owner = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($owner)->create();
		$child = Album::factory()->children_of($parent)->owned_by($owner)->create();
		$ids = $this->addPhotos($child, $owner, 3);
		$this->recompute($child);

		// No manual cover: rank 1 is the cover, sides are ranks 2, 3.
		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3("Albums/{$parent->id}")->assertOk()->json(), $child->id);
		self::assertSame([$ids[0], $ids[1], $ids[2]], [$json['cover_ids'][$i], $json['cover_ids_2'][$i], $json['cover_ids_3'][$i]]);

		// Manual cover equal to rank 2: sides are ranks 1, 3.
		$child->cover_id = $ids[1];
		$child->save();
		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3("Albums/{$parent->id}")->assertOk()->json(), $child->id);
		self::assertSame([$ids[1], $ids[0], $ids[2]], [$json['cover_ids'][$i], $json['cover_ids_2'][$i], $json['cover_ids_3'][$i]]);
	}

	// ── S-075-07: locked albums ──────────────────────────────────

	public function testLockedAlbumHidesSidesForGuest(): void
	{
		$owner = User::factory()->create();
		$locked = Album::factory()->as_root()->owned_by($owner)->create();
		AccessPermission::factory()->public()->visible()->locked()->for_album($locked)->create();
		$ids = $this->addPhotos($locked, $owner, 3);
		$this->recompute($locked);

		[$i, $json] = $this->rowOf($this->getJsonV3('Albums/root?scope=shared')->assertOk()->json(), $locked->id);
		self::assertNull($json['cover_ids'][$i]);
		self::assertNull($json['cover_ids_2'][$i]);
		self::assertNull($json['cover_ids_3'][$i]);

		// Only the manual cover is revealed by show_selected_cover_on_locked_albums; sides stay hidden.
		Configs::set('show_selected_cover_on_locked_albums', '1');
		$locked->cover_id = $ids[0];
		$locked->save();
		[$i, $json] = $this->rowOf($this->getJsonV3('Albums/root?scope=shared')->assertOk()->json(), $locked->id);
		self::assertSame($ids[0], $json['cover_ids'][$i]);
		self::assertNull($json['cover_ids_2'][$i]);
		self::assertNull($json['cover_ids_3'][$i]);

		// show_cover_of_locked_albums reveals everything.
		Configs::set('show_cover_of_locked_albums', '1');
		[$i, $json] = $this->rowOf($this->getJsonV3('Albums/root?scope=shared')->assertOk()->json(), $locked->id);
		self::assertSame([$ids[0], $ids[1], $ids[2]], [$json['cover_ids'][$i], $json['cover_ids_2'][$i], $json['cover_ids_3'][$i]]);
	}

	// ── S-075-16: every regular-album listing carries the arrays ─

	public function testPinnedAndSearchListingsCarrySideArrays(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->with_title('Sidecover needle')->create();
		DB::table('base_albums')->where('id', '=', $album->id)->update(['is_pinned' => true]);
		$ids = $this->addPhotos($album, $owner, 3);
		$this->recompute($album);

		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3('Albums/pinned?scope=own')->assertOk()->json(), $album->id);
		self::assertCount(count($json['ids']), $json['cover_ids_2']);
		self::assertCount(count($json['ids']), $json['cover_ids_3']);
		self::assertSame([$ids[1], $ids[2]], [$json['cover_ids_2'][$i], $json['cover_ids_3'][$i]]);

		[$i, $json] = $this->rowOf($this->actingAs($owner)->getJsonV3('Search/albums', ['terms' => base64_encode('Sidecover')])->assertOk()->json(), $album->id);
		self::assertCount(count($json['ids']), $json['cover_ids_2']);
		self::assertSame([$ids[1], $ids[2]], [$json['cover_ids_2'][$i], $json['cover_ids_3'][$i]]);
	}

	// ── NFR-075-01: no extra query per row ───────────────────────

	public function testChildrenListingQueryCountIsUnchanged(): void
	{
		$owner = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($owner)->create();
		$child = Album::factory()->children_of($parent)->owned_by($owner)->create();
		$this->addPhotos($child, $owner, 3);
		$this->recompute($child);
		$this->actingAs($owner);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->getJsonV3("Albums/{$parent->id}")->assertOk();
		$count = count(DB::getQueryLog());
		DB::disableQueryLog();

		self::assertSame(self::CHILDREN_QUERY_BASELINE, $count, 'GET /Albums/{id} issued ' . $count . ' queries');
	}
}
