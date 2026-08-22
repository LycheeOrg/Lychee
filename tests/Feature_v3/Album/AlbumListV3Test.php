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

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 057 Branch & Scenario Matrix S-057-01..18.
 *
 * Reuses the inherited v2 fixture graph (see
 * {@see \Tests\Feature_v2\Base\BaseApiWithDataTest} for the full diagram):
 * album1/subAlbum1 (userMayUpload1, subAlbum1 shares no permission of its
 * own), album2/subAlbum2 (userMayUpload2), album3 (userNoUpload), album4/
 * subAlbum4 (userLocked, both public+visible), album5 (admin, empty root).
 * `tagAlbum1` is a {@see \App\Models\TagAlbum}, not a real
 * {@see \App\Models\Album} row, so it is never part of this endpoint's
 * result set (mirrors `FullTree::check()`'s `Album::query()` scope).
 */
class AlbumListV3Test extends BaseApiWithDataTest
{
	/**
	 * @param string[] $ids
	 */
	private function indexOf(array $ids, string $album_id): int
	{
		$index = array_search($album_id, $ids, true);
		self::assertNotFalse($index, "album {$album_id} not found in response ids: " . implode(',', $ids));

		return $index;
	}

	/** @param string[] $tables */
	private function countTableQueries(\Closure $callback, array $tables): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$callback();
		$pattern = '/\b(' . implode('|', $tables) . ')\b/';
		$count = count(array_filter(
			DB::getQueryLog(),
			// Identifier quoting is driver-specific (SQLite/Postgres use
			// "double quotes", MySQL/MariaDB use `backticks`) — strip both
			// before matching, per Feature 053's documented
			// Illuminate\Cache\Events\* noise caveat.
			fn (array $q) => preg_match($pattern, str_replace(['"', '`'], '', $q['query'])) === 1
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		return $count;
	}

	// ── Default mode ──────────────────────────────────────────────

	/**
	 * S-057-01: Anonymous visitor sees only public/shared-without-account
	 * albums, base fields only, no parent_ids/bulk_edit.
	 */
	public function testGuestSeesOnlyPublicAlbums(): void
	{
		Auth::logout();
		$response = $this->getJsonV3('Albums');

		$response->assertOk();
		$json = $response->json();

		self::assertEqualsCanonicalizing([$this->album4->id, $this->subAlbum4->id], $json['ids']);
		self::assertNull($json['parent_ids']);
		self::assertNull($json['bulk_edit']);
		self::assertCount(count($json['ids']), $json['titles']);
		self::assertCount(count($json['ids']), $json['_lft']);
		self::assertCount(count($json['ids']), $json['_rgt']);
		self::assertCount(count($json['ids']), $json['cover_ids']);
	}

	/**
	 * S-057-02: Authenticated non-admin sees owned + shared-with-them +
	 * public, excludes private others' albums.
	 */
	public function testNonAdminSeesOwnedSharedAndPublicAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums');

		$response->assertOk();
		$ids = $response->json('ids');

		self::assertEqualsCanonicalizing(
			[$this->album1->id, $this->subAlbum1->id, $this->album4->id, $this->subAlbum4->id],
			$ids
		);
	}

	/**
	 * S-057-02 (shared branch): userMayUpload2 sees album1 via perm1 (shared
	 * with them directly), but NOT subAlbum1 — visibility (not
	 * reachability) is per-album, so a share on the parent does not imply
	 * visibility of an unshared child (Q-057-01).
	 */
	public function testNonAdminSeesAlbumSharedDirectlyButNotItsUnsharedChild(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->getJsonV3('Albums');

		$response->assertOk();
		$ids = $response->json('ids');

		self::assertContains($this->album1->id, $ids);
		self::assertNotContains($this->subAlbum1->id, $ids);
		self::assertEqualsCanonicalizing(
			[$this->album1->id, $this->album2->id, $this->subAlbum2->id, $this->album4->id, $this->subAlbum4->id],
			$ids
		);
	}

	/**
	 * S-057-08: zero visible albums → 200 with all arrays empty (not
	 * 404/500). Toggles the fixture's only public albums to link-required so
	 * a fresh guest visitor truly has nothing visible.
	 */
	public function testZeroVisibleAlbumsReturnsEmptyArrays(): void
	{
		$this->perm4->update(['is_link_required' => true]);
		$this->perm44->update(['is_link_required' => true]);

		Auth::logout();
		$response = $this->getJsonV3('Albums');

		$response->assertOk();
		$json = $response->json();

		self::assertSame([], $json['ids']);
		self::assertSame([], $json['titles']);
		self::assertSame([], $json['_lft']);
		self::assertSame([], $json['_rgt']);
		self::assertSame([], $json['cover_ids']);
	}

	/**
	 * S-057-13: a public+visible album that is ALSO password-protected but
	 * not unlocked still appears in the default listing — visibility, not
	 * reachability, governs this endpoint (Q-057-01 regression guard).
	 */
	public function testPasswordLockedButVisibleAlbumStillAppears(): void
	{
		$locked_album = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		AccessPermission::factory()->public()->visible()->locked()->for_album($locked_album)->create();

		Auth::logout();
		$response = $this->getJsonV3('Albums');

		$response->assertOk();
		self::assertContains($locked_album->id, $response->json('ids'));
	}

	/**
	 * The response is a bare SoA object with no pagination envelope
	 * (NFR-057-02).
	 */
	public function testResponseHasNoPaginationEnvelope(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums');

		$response->assertOk();
		$json = $response->json();

		self::assertEqualsCanonicalizing(['ids', 'titles', '_lft', '_rgt', 'cover_ids', 'parent_ids', 'bulk_edit'], array_keys($json));
	}

	// ── cover_ids resolution (FR-057-09) ─────────────────────────

	/**
	 * S-057-15: explicit cover_id wins regardless of viewer privilege.
	 */
	public function testExplicitCoverIdWinsRegardlessOfViewer(): void
	{
		DB::table('albums')->where('id', '=', $this->album1->id)->update([
			'cover_id' => $this->photo1->id,
			'auto_cover_id_max_privilege' => $this->photo1b->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums');
		$response->assertOk();
		$json = $response->json();
		$idx = $this->indexOf($json['ids'], $this->album1->id);
		self::assertSame($this->photo1->id, $json['cover_ids'][$idx]);
	}

	/**
	 * S-057-16: owner (no explicit cover) sees auto_cover_id_max_privilege.
	 */
	public function testOwnerSeesMaxPrivilegeCoverWhenNoExplicitCover(): void
	{
		DB::table('albums')->where('id', '=', $this->album1->id)->update([
			'cover_id' => null,
			'auto_cover_id_max_privilege' => $this->photo1->id,
			'auto_cover_id_least_privilege' => null,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums');
		$response->assertOk();
		$json = $response->json();
		$idx = $this->indexOf($json['ids'], $this->album1->id);
		self::assertSame($this->photo1->id, $json['cover_ids'][$idx]);
	}

	/**
	 * S-057-17: neither owner nor admin (no explicit cover) sees
	 * auto_cover_id_least_privilege.
	 */
	public function testOtherViewerSeesLeastPrivilegeCoverWhenNoExplicitCover(): void
	{
		DB::table('albums')->where('id', '=', $this->album4->id)->update([
			'cover_id' => null,
			'auto_cover_id_max_privilege' => $this->photo4->id,
			'auto_cover_id_least_privilege' => $this->photo4->id,
		]);

		Auth::logout();
		$response = $this->getJsonV3('Albums');
		$response->assertOk();
		$json = $response->json();
		$idx = $this->indexOf($json['ids'], $this->album4->id);
		self::assertSame($this->photo4->id, $json['cover_ids'][$idx]);
	}

	/**
	 * S-057-18: none of the three cover columns set → null, no fallback
	 * query.
	 */
	public function testNoCoverColumnsSetYieldsNullCover(): void
	{
		DB::table('albums')->where('id', '=', $this->album5->id)->update([
			'cover_id' => null,
			'auto_cover_id_max_privilege' => null,
			'auto_cover_id_least_privilege' => null,
		]);

		$response = $this->actingAs($this->admin)->getJsonV3('Albums');
		$response->assertOk();
		$json = $response->json();
		$idx = $this->indexOf($json['ids'], $this->album5->id);
		self::assertNull($json['cover_ids'][$idx]);
	}

	// ── with_parent_id ────────────────────────────────────────────

	/**
	 * S-057-03: non-admin + with_parent_id=true → 403.
	 */
	public function testNonAdminWithParentIdIsForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums', ['with_parent_id' => true]);
		$response->assertForbidden();
	}

	/**
	 * S-057-05/S-057-12: admin + with_parent_id=true → correct parent_ids,
	 * null (not omitted) for root albums, full index alignment.
	 */
	public function testAdminWithParentIdReturnsCorrectParentIds(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums', ['with_parent_id' => true]);
		$response->assertOk();
		$json = $response->json();

		self::assertNotNull($json['parent_ids']);
		self::assertCount(count($json['ids']), $json['parent_ids']);

		$root_idx = $this->indexOf($json['ids'], $this->album1->id);
		self::assertNull($json['parent_ids'][$root_idx]);

		$child_idx = $this->indexOf($json['ids'], $this->subAlbum1->id);
		self::assertSame($this->album1->id, $json['parent_ids'][$child_idx]);
	}

	// ── for_bulk_edit ─────────────────────────────────────────────

	/**
	 * S-057-04: non-admin + for_bulk_edit=true → 403.
	 */
	public function testNonAdminForBulkEditIsForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums', ['for_bulk_edit' => true]);
		$response->assertForbidden();
	}

	/**
	 * S-057-06: admin + for_bulk_edit=true → bulk-edit block field values
	 * match the equivalent BulkAlbumResource-style computation.
	 */
	public function testAdminForBulkEditReturnsFieldParityValues(): void
	{
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update([
			'copyright' => '(c) Test',
			'photo_layout' => 'square',
			'sorting_col' => 'created_at',
			'sorting_order' => 'DESC',
			'photo_timeline' => 'day',
			'description' => 'desc text',
		]);
		DB::table('albums')->where('id', '=', $this->album1->id)->update([
			'license' => 'CC0',
			'album_sorting_col' => 'title',
			'album_sorting_order' => 'ASC',
			'album_thumb_aspect_ratio' => '1/1',
			'album_timeline' => 'year',
		]);

		$response = $this->actingAs($this->admin)->getJsonV3('Albums', ['for_bulk_edit' => true]);
		$response->assertOk();
		$json = $response->json();

		self::assertNotNull($json['bulk_edit']);
		$idx = $this->indexOf($json['ids'], $this->album1->id);
		$bulk_edit = $json['bulk_edit'];

		self::assertSame($this->userMayUpload1->id, $bulk_edit['owner_ids'][$idx]);
		self::assertSame($this->userMayUpload1->name, $bulk_edit['owner_names'][$idx]);
		self::assertSame('desc text', $bulk_edit['descriptions'][$idx]);
		self::assertSame('(c) Test', $bulk_edit['copyrights'][$idx]);
		self::assertSame('CC0', $bulk_edit['licenses'][$idx]);
		self::assertSame('square', $bulk_edit['photo_layouts'][$idx]);
		self::assertSame('created_at', $bulk_edit['photo_sorting_cols'][$idx]);
		self::assertSame('DESC', $bulk_edit['photo_sorting_orders'][$idx]);
		self::assertSame('title', $bulk_edit['album_sorting_cols'][$idx]);
		self::assertSame('ASC', $bulk_edit['album_sorting_orders'][$idx]);
		self::assertSame('1/1', $bulk_edit['album_thumb_aspect_ratios'][$idx]);
		self::assertSame('year', $bulk_edit['album_timelines'][$idx]);
		self::assertSame('day', $bulk_edit['photo_timelines'][$idx]);
		self::assertFalse($bulk_edit['is_nsfws'][$idx]);
		self::assertFalse($bulk_edit['is_publics'][$idx]);
		self::assertFalse($bulk_edit['is_link_requireds'][$idx]);
		self::assertFalse($bulk_edit['grants_full_photo_accesses'][$idx]);
		self::assertFalse($bulk_edit['grants_downloads'][$idx]);
		self::assertFalse($bulk_edit['grants_uploads'][$idx]);
		self::assertNotNull($bulk_edit['created_ats'][$idx]);
	}

	/**
	 * S-057-06 (public branch): a publicly-shared album's is_public/
	 * is_link_required/grants_* reflect the *public* access_permissions row,
	 * independent of the viewer-scoped visibility join.
	 */
	public function testAdminForBulkEditReflectsPublicPermissionRow(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums', ['for_bulk_edit' => true]);
		$response->assertOk();
		$json = $response->json();
		$bulk_edit = $json['bulk_edit'];

		$idx = $this->indexOf($json['ids'], $this->album4->id);
		self::assertTrue($bulk_edit['is_publics'][$idx]);
		self::assertFalse($bulk_edit['is_link_requireds'][$idx]);
	}

	/**
	 * S-057-07: both flags combined — both blocks present simultaneously.
	 */
	public function testAdminBothFlagsCombined(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums', ['with_parent_id' => true, 'for_bulk_edit' => true]);
		$response->assertOk();
		$json = $response->json();

		self::assertNotNull($json['parent_ids']);
		self::assertNotNull($json['bulk_edit']);

		$idx = $this->indexOf($json['ids'], $this->subAlbum1->id);
		self::assertSame($this->album1->id, $json['parent_ids'][$idx]);
		self::assertSame($this->userMayUpload1->id, $json['bulk_edit']['owner_ids'][$idx]);
	}

	// ── Managed cache (Feature 052/053 integration) ──────────────

	/**
	 * S-057-09: second identical request within TTL is served from cache —
	 * no repeat query against albums/base_albums/access_permissions/users.
	 */
	public function testCacheHitPerformsNoRelevantTableQueries(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk();

		$second_call_count = $this->countTableQueries(
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk(),
			['albums', 'base_albums', 'access_permissions', 'users']
		);

		self::assertSame(0, $second_call_count, 'A cache hit must not run any query against albums/base_albums/access_permissions/users.');
	}

	/**
	 * S-057-11: either cache toggle false → endpoint stays correct, but
	 * uncached (query re-executed every call).
	 */
	public function testCacheDisabledStillCorrectButUncached(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '0');

		$this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk();

		$second_call_count = $this->countTableQueries(
			fn () => $this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk(),
			['albums', 'base_albums', 'access_permissions', 'users']
		);

		self::assertGreaterThan(0, $second_call_count, 'managed_cache_albums_enabled=false must disable caching for this endpoint.');
	}

	/**
	 * S-057-14: two different identities never share a cache entry — each
	 * gets their own correctly-curated result.
	 */
	public function testNoCrossIdentityCacheLeakage(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$guest_ids = $this->getJsonV3('Albums')->assertOk()->json('ids');
		$user_ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk()->json('ids');

		self::assertNotEquals($guest_ids, $user_ids);
		self::assertContains($this->album1->id, $user_ids);
		self::assertNotContains($this->album1->id, $guest_ids);
	}

	/**
	 * S-057-10: an album mutation between two requests invalidates the
	 * cache — the second request reflects the change. Uses the v2
	 * create-album endpoint purely as a mutation trigger (this v3 endpoint
	 * is read-only, per spec Non-Goals); the assertion below is entirely
	 * about this v3 endpoint's own response.
	 */
	public function testCacheInvalidatedOnAlbumMutation(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$before_ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk()->json('ids');

		$create_response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => null,
			'title' => 'Feature-057-new-album',
		]);
		self::assertSame(200, $create_response->getStatusCode());
		$new_album_id = $create_response->getOriginalContent();

		$after_ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums')->assertOk()->json('ids');

		self::assertNotContains($new_album_id, $before_ids);
		self::assertContains($new_album_id, $after_ids);
	}
}
