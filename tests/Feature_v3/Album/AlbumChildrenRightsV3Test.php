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
use App\Models\Face;
use App\Models\Person;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\DB;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
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

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/rights");
		$this->assertForbidden($response);
	}

	// ── Shape / whole-vs-per-child split ──────────────────────────

	public function testResponseShapeAndWholeVsPerChildSplit(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/rights");
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

		$children_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}")->assertOk()->json('ids');
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

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();

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

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();

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

		$json = $this->actingAs($caller)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();
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
		$json = $this->actingAs($this->admin)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();
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
		$json = $this->getJsonV3("Albums/{$this->album4->id}/rights")->assertOk()->json();

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

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/rights")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/rights")->assertOk();
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

		$before = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights");
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

		$after = $this->actingAs($this->userMayUpload2)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json();
		self::assertTrue($after['can_delete_children']);
	}

	// ── Visibility parity (T-061-37) ───────────────────────────────

	public function testVisibilityParityWithChildrenEndpoint(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($visible);

		$owner_rights_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/rights")->assertOk()->json('ids');
		$owner_children_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json('ids');

		self::assertEqualsCanonicalizing($owner_children_ids, $owner_rights_ids);

		// Parent itself is private -> a stranger can't even resolve it.
		$this->assertForbidden($this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/rights"));
	}

	// ── TagAlbum / PersonAlbum "matching albums" support ───────────

	/**
	 * A matching-albums result has no single shared parent whose grants
	 * could uniformly apply — can_delete_children/can_move_children are
	 * always false, even for an otherwise-fully-granted caller.
	 */
	public function testTagAlbumCanDeleteChildrenIsAlwaysFalse(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/rights")->assertOk()->json();

		self::assertSame([$this->album1->id], $json['ids']);
		self::assertFalse($json['can_delete_children']);
		self::assertFalse($json['can_move_children']);
		self::assertSame((string) $this->tagAlbum1->owner_id, $json['owner_id']);
	}

	/**
	 * Fixed CodeRabbit finding on PR #4680: mirrors
	 * AlbumChildrenDataV3Test::testDifferentUnlockStatesDoNotShareACacheEntry()
	 * for the rights endpoint - it shares the exact same cache-key gap since
	 * it also computes its TagAlbum/PersonAlbum branch from the session-scoped
	 * AlbumPolicy::getUnlockedAlbumIDs() state.
	 */
	public function testDifferentUnlockStatesDoNotShareACacheEntry(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$password_album = $this->album2;
		$password_album->tags()->sync([$this->tag_test->id]);
		AccessPermission::factory()->public()->visible()->locked()->for_album($password_album)->create();

		$response_locked = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/rights");
		$this->assertOk($response_locked);
		self::assertSame([], $response_locked->json('ids'));

		session()->push(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, $password_album->id);
		$response_unlocked = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/rights");
		$this->assertOk($response_unlocked);
		self::assertSame([$password_album->id], $response_unlocked->json('ids'));
	}

	public function testTagAlbumGrantsEditStillReflectsPerAlbumGrant(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);
		// userNoUpload has no pre-existing grant on album1 (unlike
		// userMayUpload2, which already carries the base fixture's perm1).
		// Access to tagAlbum1 itself (separate from access to any of its
		// matching albums) is also required to call this endpoint at all.
		AccessPermission::factory()->for_user($this->userNoUpload)->visible()->create(['base_album_id' => $this->tagAlbum1->id]);
		AccessPermission::factory()
			->for_user($this->userNoUpload)
			->for_album($this->album1)
			->visible()
			->grants_edit()
			->create();

		$json = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->tagAlbum1->id}/rights")->assertOk()->json();

		self::assertSame([$this->album1->id], $json['ids']);
		self::assertTrue($json['grants_edit'][0]);
		self::assertFalse($json['grants_download'][0]);
	}

	public function testTagAlbumAdminCanDeleteChildrenStillFalseButGrantsTrue(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);

		$json = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->tagAlbum1->id}/rights")->assertOk()->json();

		self::assertFalse($json['can_delete_children']);
		self::assertFalse($json['can_move_children']);
		self::assertTrue($json['grants_edit'][0]);
		self::assertTrue($json['grants_download'][0]);
	}

	public function testPersonAlbumCanDeleteChildrenIsAlwaysFalse(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		Face::factory()->for_photo($this->photo1)->for_person($person)->create();

		$create_response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_alice',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($create_response);
		$person_album_id = $create_response->getOriginalContent();

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$person_album_id}/rights")->assertOk()->json();

		self::assertSame([$this->album1->id], $json['ids']);
		self::assertFalse($json['can_delete_children']);
		self::assertFalse($json['can_move_children']);
	}

	// ── Cache invalidation on group-membership change ──────────────

	/**
	 * Fixed CodeRabbit finding on PR #4680: mirrors
	 * AlbumChildrenDataV3Test::testCacheInvalidatedOnGroupMembershipChange()
	 * for the rights endpoint.
	 */
	public function testCacheInvalidatedOnGroupMembershipChange(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($parent)->create();

		$hidden_child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($hidden_child);
		AccessPermission::factory()->for_user_group($this->group1)->for_album($hidden_child)->visible()->grants_edit()->create();

		$before = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/rights");
		$this->assertOk($before);
		self::assertSame([], $before->json('ids'));

		$add_response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->postJson('UserGroups/Users', [
			'group_id' => $this->group1->id,
			'user_id' => $this->userNoUpload->id,
			'role' => 'member',
		]);
		$this->assertCreated($add_response);

		$this->userNoUpload->unsetRelation('user_groups')->refresh();

		$after = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/rights");
		$this->assertOk($after);
		self::assertSame([$hidden_child->id], $after->json('ids'));
		self::assertTrue($after->json('grants_edit')[0]);
	}
}
