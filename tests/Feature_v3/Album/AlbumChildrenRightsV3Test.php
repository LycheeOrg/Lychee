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

use App\Enum\UserGroupRole;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 061 FR-061-19..23, S-061-34..41.
 */
class AlbumChildrenRightsV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403RegardlessOfCallerRights(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/children/rights");
		$this->assertForbidden($response);
	}

	// ── Shape / whole-vs-per-child split ──────────────────────────

	public function testResponseShapeAndWholeVsPerChildSplit(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/children/rights");
		$this->assertOk($response);
		$json = $response->json();

		foreach (['owner_id', 'can_delete_children', 'can_move_children', 'ids', 'grants_edit', 'grants_download'] as $field) {
			self::assertArrayHasKey($field, $json);
		}
		self::assertIsString($json['owner_id']);
		self::assertIsBool($json['can_delete_children']);
		self::assertIsBool($json['can_move_children']);
		self::assertIsArray($json['ids']);
		self::assertSame((string) $this->userMayUpload1->id, $json['owner_id']);

		$children_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/children")->assertOk()->json('ids');
		self::assertEqualsCanonicalizing($children_ids, $json['ids']);
	}

	// ── Per-child grant vs whole-response parent-scoped rights (T-061-30) ──

	public function testPerChildGrantOnlyAffectsThatChild(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$shared_child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$sibling = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($shared_child);
		$this->recompute($sibling);

		// Visibility into the parent and both children is a prerequisite
		// (sharing a parent does not cascade visibility to its children,
		// same as v2's queryChildrenPaginated()) — grant plain visibility
		// on the parent and both children first, then layer the one
		// child-specific grants_edit on top.
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($parent)->visible()->create();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($sibling)->visible()->create();
		AccessPermission::factory()
			->for_user($this->userMayUpload2)
			->for_album($shared_child)
			->visible()
			->grants_edit()
			->create();

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json();

		$idx_shared = array_search($shared_child->id, $json['ids'], true);
		$idx_sibling = array_search($sibling->id, $json['ids'], true);

		self::assertTrue($json['grants_edit'][$idx_shared]);
		self::assertFalse($json['grants_edit'][$idx_sibling]);
	}

	public function testCanDeleteChildrenComesFromGrantOnParentNotAnyChild(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		// The parent's own grant carries can_delete_children; the child
		// needs its own separate visibility grant to appear in the listing
		// at all (sharing a parent does not cascade to its children).
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($child)->visible()->create();
		AccessPermission::factory()
			->for_user($this->userMayUpload2)
			->for_album($parent)
			->visible()
			->grants_delete()
			->create();

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json();

		self::assertTrue($json['can_delete_children']);
		self::assertTrue($json['can_move_children']);
		// The grant is on the parent, not the child -> no per-child grant.
		self::assertFalse($json['grants_edit'][0]);
	}

	// ── Multi-group overlap correctness (T-061-31, the critical test) ──

	public function testMultiGroupOverlappingGrantsMergeCorrectlyAndMatchDirectPolicyCalls(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$shared_child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($shared_child);

		$caller = User::factory()->with_group($this->group1)->create();
		$caller->user_groups()->attach($this->group2, ['role' => UserGroupRole::MEMBER]);

		// Plain visibility into the parent (sharing a parent does not
		// cascade to its children) — group1 grants edit, group2 grants
		// download on the *same* child.
		AccessPermission::factory()->for_user_group($this->group1)->for_album($parent)->visible()->create();
		AccessPermission::factory()->for_user_group($this->group1)->for_album($shared_child)->visible()->grants_edit()->create();
		AccessPermission::factory()->for_user_group($this->group2)->for_album($shared_child)->visible()->grants_download()->create();

		$json = $this->actingAs($caller)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json();
		$idx = array_search($shared_child->id, $json['ids'], true);

		self::assertTrue($json['grants_edit'][$idx], 'grants_edit must be true (MAX-merged from group1), not arbitrarily overwritten by group2\'s row.');
		self::assertTrue($json['grants_download'][$idx], 'grants_download must be true (MAX-merged from group2), not arbitrarily overwritten by group1\'s row.');

		// Cross-check against direct AlbumPolicy calls for the same child/caller.
		$this->actingAs($caller);
		$shared_child->refresh();
		$policy = resolve(AlbumPolicy::class);
		self::assertSame($policy->canEdit($caller, $shared_child), $json['grants_edit'][$idx]);
		self::assertSame($policy->canDownload($caller, $shared_child), $json['grants_download'][$idx]);
	}

	// ── Admin / guest branches (T-061-32) ──────────────────────────

	public function testAdminCallerGetsAllRightsTrueWithoutRunningTheJoinOrExistsQuery(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$json = $this->actingAs($this->admin)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertTrue($json['can_delete_children']);
		self::assertTrue($json['can_move_children']);
		self::assertTrue($json['grants_edit'][0]);
		self::assertTrue($json['grants_download'][0]);

		$grants_join_queries = array_filter($log, fn (array $q) => preg_match('/grants_computed_access_permissions/i', $q['query']) === 1);
		self::assertCount(0, $grants_join_queries, 'Admin must never run the grants-join query.');
		$exists_queries = array_filter($log, fn (array $q) => preg_match('/grants_delete/i', $q['query']) === 1);
		self::assertCount(0, $exists_queries, 'Admin must never run the can_delete_children exists() query.');
	}

	public function testGuestCallerOnlyReflectsPublicGrants(): void
	{
		// subAlbum4/album4 are public with grants set via perm4/perm44 fixtures.
		$json = $this->getJsonV3("Albums/{$this->album4->id}/children/rights")->assertOk()->json();

		self::assertContains($this->subAlbum4->id, $json['ids']);
		self::assertFalse($json['can_delete_children']);
	}

	// ── Cache (T-061-35) ───────────────────────────────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$relevant = array_filter($log, fn (array $q) => preg_match('/group by "albums"."id"/i', $q['query']) === 1);
		self::assertCount(0, $relevant, 'A cache hit must not re-run the rights aggregation query.');
	}

	/**
	 * S-061-40: a grant change against the queried parent itself invalidates
	 * this endpoint's cache (verifies the FR-061-22 fix to
	 * ManagedCacheAlbumListingInvalidator::handleAccessPermissionChanged()).
	 */
	public function testCacheInvalidatedOnPermissionChangeAgainstTheParent(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$before = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/children/rights");
		$this->assertForbidden($before);

		$share_response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'album_ids' => [$parent->id],
			'user_ids' => [$this->userMayUpload2->id],
			'group_ids' => [],
			'grants_download' => false,
			'grants_full_photo_access' => false,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => true,
		]);
		self::assertSame(200, $share_response->getStatusCode());

		$after = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json();
		self::assertTrue($after['can_delete_children']);
	}

	// ── Visibility parity (T-061-37) ───────────────────────────────

	public function testVisibilityParityWithChildrenEndpoint(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($visible);

		$owner_rights_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children/rights")->assertOk()->json('ids');
		$owner_children_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/children")->assertOk()->json('ids');

		self::assertEqualsCanonicalizing($owner_children_ids, $owner_rights_ids);

		// Parent itself is private -> a stranger can't even resolve it.
		$this->assertForbidden($this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/children/rights"));
	}
}
