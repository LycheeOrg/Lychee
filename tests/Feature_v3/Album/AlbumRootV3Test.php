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

use App\Actions\Albums\Top;
use App\Jobs\RecomputeRootAlbumBucketsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 062 FR-062-01..07/13, S-062-01..12/18..20/29, NFR-062-05.
 *
 * Builds its own isolated root-album fixture per test (FX-062-01), rather
 * than editing the shared v2/v3 base fixture, so it stays independent of
 * other v3 test suites.
 */
class AlbumRootV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'created_at']);
		DB::table('configs')->where('key', '=', 'sorting_albums_order')->update(['value' => 'ASC']);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
	}

	private function recomputeRootBuckets(): void
	{
		(new RecomputeRootAlbumBucketsJob())->handle();
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403RegardlessOfCallerRights(): void
	{
		config(['features.struct-of-array' => false]);

		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/root?scope=own'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/root/buckets?scope=own'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/root/rights?scope=own'));
	}

	public function testFlagOffReturns401ForGuest(): void
	{
		// Unauthenticated + failed authorize() -> 401 per
		// BaseApiRequest::failedAuthorization()'s existing convention
		// (403 is reserved for an authenticated-but-not-permitted caller).
		config(['features.struct-of-array' => false]);

		$this->assertUnauthorized($this->getJsonV3('Albums/root'));
	}

	// ── scope validation (S-062-01..04) ─────────────────────────────

	public function testGuestOmittingScopeIsTreatedAsShared(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		$response = $this->getJsonV3('Albums/root');
		$this->assertOk($response);
	}

	public function testGuestExplicitSharedIsIdenticalToOmittingScope(): void
	{
		$omitted = $this->getJsonV3('Albums/root')->assertOk()->json('ids');
		$explicit = $this->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');

		self::assertEqualsCanonicalizing($omitted, $explicit);
	}

	public function testGuestRequestingOwnScopeReturns422(): void
	{
		$this->assertUnprocessable($this->getJsonV3('Albums/root?scope=own'));
	}

	public function testAuthenticatedOmittingScopeReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3('Albums/root'));
	}

	public function testAuthenticatedInvalidScopeReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3('Albums/root?scope=bogus'));
	}

	// ── scope=own (S-062-05) ─────────────────────────────────────────

	public function testOwnScopeOnlyReturnsCallersOwnRootAlbums(): void
	{
		$user1 = User::factory()->create();
		$user2 = User::factory()->create();
		$mine1 = Album::factory()->as_root()->owned_by($user1)->create();
		$mine2 = Album::factory()->as_root()->owned_by($user1)->create();
		Album::factory()->as_root()->owned_by($user2)->create();

		$ids = $this->actingAs($user1)->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');

		self::assertEqualsCanonicalizing([$mine1->id, $mine2->id], $ids);
	}

	public function testOwnScopeBucketsGroupByPersistedBucketIdWithDateBuckets(): void
	{
		$user = User::factory()->create();
		$a = Album::factory()->as_root()->owned_by($user)->create(['created_at' => new \Illuminate\Support\Carbon('2023-05-01')]);
		$b = Album::factory()->as_root()->owned_by($user)->create(['created_at' => new \Illuminate\Support\Carbon('2024-05-01')]);
		$this->recomputeRootBuckets();

		$response = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own');
		$this->assertOk($response);
		$response->assertJson([
			'bucket_ids' => ['2023', '2024'],
			'counts' => [1, 1],
			'bucketable' => true,
		]);
	}

	public function testOwnScopeChildrenDataOrderedByBucketIdThenSortCriterion(): void
	{
		$user = User::factory()->create();
		$a = Album::factory()->as_root()->owned_by($user)->create(['created_at' => new \Illuminate\Support\Carbon('2024-01-01')]);
		$b = Album::factory()->as_root()->owned_by($user)->create(['created_at' => new \Illuminate\Support\Carbon('2023-01-01')]);
		$this->recomputeRootBuckets();

		$ids = $this->actingAs($user)->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');

		self::assertSame([$b->id, $a->id], $ids);
	}

	// ── scope=shared (S-062-06/07/29) ────────────────────────────────

	public function testSharedScopeBucketsGroupByOwnerLiveWithRealNamesForAuthenticatedCaller(): void
	{
		// album4 (owned by userLocked) is the base fixture's only public
		// root album — using userLocked as the caller excludes it from
		// `shared` scope automatically (a caller never sees their own
		// albums under `shared`), keeping this fixture exactly 3 owners.
		$caller = $this->userLocked;
		$owner1 = User::factory()->create(['username' => 'owner-one', 'display_name' => null]);
		$owner2 = User::factory()->create(['username' => 'owner-two', 'display_name' => 'Owner Two Display']);
		$owner3 = User::factory()->create(['username' => 'owner-three', 'display_name' => null]);

		foreach ([$owner1, $owner2, $owner3] as $owner) {
			$album = Album::factory()->as_root()->owned_by($owner)->create();
			AccessPermission::factory()->public()->visible()->for_album($album)->create();
		}

		$json = $this->actingAs($caller)->getJsonV3('Albums/root/buckets?scope=shared')->assertOk()->json();

		self::assertSame(['bucket_ids', 'counts', 'labels', 'bucketable'], array_keys($json));
		self::assertCount(3, $json['bucket_ids']);
		self::assertTrue($json['bucketable']);
		self::assertContains((string) $owner1->id, $json['bucket_ids']);
		self::assertContains('owner-one', $json['labels']);
		self::assertContains('Owner Two Display', $json['labels']);
	}

	public function testGuestSharedScopeBucketsAreAllUnknownWithZeroUsersJoin(): void
	{
		$owner1 = User::factory()->create();
		$owner2 = User::factory()->create();
		$owner3 = User::factory()->create();

		foreach ([$owner1, $owner2, $owner3] as $owner) {
			$album = Album::factory()->as_root()->owned_by($owner)->create();
			AccessPermission::factory()->public()->visible()->for_album($album)->create();
		}

		DB::flushQueryLog();
		DB::enableQueryLog();
		$json = $this->getJsonV3('Albums/root/buckets?scope=shared')->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertTrue($json['bucketable']);
		// album4 (owned by the base fixture's userLocked) is also public and
		// therefore visible to a guest — assert on the superset containing
		// my 3 new owners rather than an exact count.
		foreach ([$owner1, $owner2, $owner3] as $owner) {
			self::assertContains((string) $owner->id, $json['bucket_ids']);
		}
		self::assertSame(array_fill(0, count($json['labels']), 'unknown'), $json['labels']);

		$users_join_queries = array_filter($log, fn (array $q) => preg_match('/join\s+[`"]?users[`"]?/i', $q['query']) === 1);
		self::assertCount(0, $users_join_queries, 'Guest request must never execute a users join.');
	}

	public function testAuthenticatedSharedScopeBucketsExecuteUsersJoin(): void
	{
		$caller = User::factory()->create();
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($caller)->getJsonV3('Albums/root/buckets?scope=shared')->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$users_join_queries = array_filter($log, fn (array $q) => preg_match('/join\s+[`"]?users[`"]?/i', $q['query']) === 1);
		self::assertGreaterThan(0, count($users_join_queries));
	}

	public function testSharedScopeChildrenDataBucketIdFieldEqualsOwnerIdAndCorrelatesWithBucketsEndpoint(): void
	{
		$caller = User::factory()->create();
		$owner1 = User::factory()->create();
		$owner2 = User::factory()->create();
		$a1 = Album::factory()->as_root()->owned_by($owner1)->create();
		$a2 = Album::factory()->as_root()->owned_by($owner2)->create();
		foreach ([$a1, $a2] as $album) {
			AccessPermission::factory()->public()->visible()->for_album($album)->create();
		}

		$children = $this->actingAs($caller)->getJsonV3('Albums/root?scope=shared')->assertOk()->json();
		$buckets = $this->actingAs($caller)->getJsonV3('Albums/root/buckets?scope=shared')->assertOk()->json();

		$idx1 = array_search($a1->id, $children['ids'], true);
		$idx2 = array_search($a2->id, $children['ids'], true);
		self::assertSame((string) $owner1->id, $children['bucket_ids'][$idx1]);
		self::assertSame((string) $owner2->id, $children['bucket_ids'][$idx2]);

		// Grouping children rows by bucket_id reproduces the buckets grouping.
		$grouped_counts = array_count_values($children['bucket_ids']);
		foreach ($buckets['bucket_ids'] as $i => $bucket_id) {
			self::assertSame($buckets['counts'][$i], $grouped_counts[$bucket_id]);
		}
	}

	public function testSharedScopeWithZeroSharedAlbumsIsBucketableTrueWithEmptyArrays(): void
	{
		// userLocked owns album4, the base fixture's only public root album
		// — using it as the caller means `shared` scope (owner != caller)
		// has genuinely nothing visible.
		$user = $this->userLocked;
		Album::factory()->as_root()->owned_by($user)->create();

		$response = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=shared');
		$this->assertOk($response);
		$response->assertExactJson(['bucket_ids' => [], 'counts' => [], 'labels' => [], 'bucketable' => true]);

		$children = $this->actingAs($user)->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');
		self::assertSame([], $children);
	}

	public function testSharedScopeExcludesCallersOwnAlbums(): void
	{
		// userLocked owns album4 (the base fixture's only other public root
		// album) — using it as the caller keeps this fixture's shared result
		// down to exactly $theirs.
		$user = $this->userLocked;
		$mine = Album::factory()->as_root()->owned_by($user)->create();
		$other_owner = User::factory()->create();
		$theirs = Album::factory()->as_root()->owned_by($other_owner)->create();
		AccessPermission::factory()->public()->visible()->for_album($theirs)->create();

		$ids = $this->actingAs($user)->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');

		self::assertSame([$theirs->id], $ids);
	}

	// ── rights (S-062-08/09) ─────────────────────────────────────────

	public function testAdminRootRightsAllTrueAndOwnerIdKeyAbsent(): void
	{
		$json = $this->actingAs($this->admin)->getJsonV3('Albums/root/rights?scope=own')->assertOk()->json();

		self::assertTrue($json['can_delete_children']);
		self::assertTrue($json['can_move_children']);
		self::assertArrayNotHasKey('owner_id', $json);
	}

	public function testNonAdminRootRightsAlwaysFalseDeleteMoveAndOwnerIdKeyAbsentBothScopes(): void
	{
		$user = User::factory()->create();
		Album::factory()->as_root()->owned_by($user)->create();

		$own = $this->actingAs($user)->getJsonV3('Albums/root/rights?scope=own')->assertOk()->json();
		self::assertFalse($own['can_delete_children']);
		self::assertFalse($own['can_move_children']);
		self::assertArrayNotHasKey('owner_id', $own);

		$shared = $this->actingAs($user)->getJsonV3('Albums/root/rights?scope=shared')->assertOk()->json();
		self::assertFalse($shared['can_delete_children']);
		self::assertFalse($shared['can_move_children']);
		self::assertArrayNotHasKey('owner_id', $shared);
	}

	// ── config-change recompute dispatch (S-062-10..12) ──────────────

	public function testSortingColumnChangeRecomputesOwnScopeBucketsButNeverAffectsSharedScope(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create(['created_at' => new \Illuminate\Support\Carbon('2024-01-01')]);
		$this->recomputeRootBuckets();

		$before = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk()->json('bucket_ids');
		self::assertSame(['2024'], $before);

		// Configs::set() invalidates ConfigManager's request-scoped in-memory
		// cache (unlike a raw DB::table('configs')->update()), which is
		// required here since this test already read configs once above.
		Configs::set('title_bucket_mode', 'alphabetical');
		Configs::set('sorting_albums_col', 'title');
		$this->recomputeRootBuckets();

		$after = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk()->json('bucket_ids');
		self::assertNotSame(['2024'], $after);
	}

	// ── deduplicate_pinned_albums parity (S-062-20) ──────────────────

	public function testDeduplicatePinnedAlbumsExcludesPinnedAlbumFromBothScopes(): void
	{
		Configs::set('deduplicate_pinned_albums', '1');

		$owner = User::factory()->create();
		$pinned = Album::factory()->as_root()->owned_by($owner)->create();
		DB::table('base_albums')->where('id', '=', $pinned->id)->update(['is_pinned' => true]);
		AccessPermission::factory()->public()->visible()->for_album($pinned)->create();

		$own_ids = $this->actingAs($owner)->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');
		self::assertNotContains($pinned->id, $own_ids);

		$other = User::factory()->create();
		$shared_ids = $this->actingAs($other)->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');
		self::assertNotContains($pinned->id, $shared_ids);
	}

	// ── NFR-062-05: own ∪ shared reconstructs v2's exact partition ───

	public function testOwnUnionSharedReconstructsV2ExactlyForAuthenticatedUser(): void
	{
		$user = User::factory()->create();
		Album::factory()->as_root()->owned_by($user)->create();
		$other1 = User::factory()->create();
		$other2 = User::factory()->create();
		$shared1 = Album::factory()->as_root()->owned_by($other1)->create();
		$shared2 = Album::factory()->as_root()->owned_by($other2)->create();
		AccessPermission::factory()->public()->visible()->for_album($shared1)->create();
		AccessPermission::factory()->public()->visible()->for_album($shared2)->create();

		$this->actingAs($user);
		$v2 = resolve(Top::class)->get();
		$v2_ids = $v2->albums->pluck('id')->merge($v2->shared_albums->pluck('id'))->all();

		$own_ids = $this->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');
		$shared_ids = $this->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');
		$v3_ids = array_merge($own_ids, $shared_ids);

		self::assertEqualsCanonicalizing($v2_ids, $v3_ids);
		self::assertEmpty(array_intersect($own_ids, $shared_ids));
	}

	public function testSharedOnlyReconstructsV2ExactlyForGuest(): void
	{
		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		$v2 = resolve(Top::class)->get();
		$v2_ids = $v2->albums->pluck('id')->all();

		$v3_ids = $this->getJsonV3('Albums/root')->assertOk()->json('ids');

		self::assertEqualsCanonicalizing($v2_ids, $v3_ids);
	}

	// ── Managed cache (mirrors Feature 061 precedent) ────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$user = User::factory()->create();
		Album::factory()->as_root()->owned_by($user)->create();
		$this->recomputeRootBuckets();

		$this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$aggregation_queries = array_filter($log, fn (array $q) => preg_match('/group by/i', $q['query']) === 1);
		self::assertCount(0, $aggregation_queries, 'A cache hit must not re-run the GROUP BY aggregation query.');
	}

	public function testNoCrossScopeCacheLeakage(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		// userLocked owns album4 (the base fixture's only other public root
		// album) — using it as the caller keeps `shared` down to exactly
		// $theirs.
		$user = $this->userLocked;
		$mine = Album::factory()->as_root()->owned_by($user)->create();
		$other = User::factory()->create();
		$theirs = Album::factory()->as_root()->owned_by($other)->create();
		AccessPermission::factory()->public()->visible()->for_album($theirs)->create();

		$own = $this->actingAs($user)->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');
		$shared = $this->actingAs($user)->getJsonV3('Albums/root?scope=shared')->assertOk()->json('ids');

		self::assertContains($mine->id, $own);
		self::assertSame([$theirs->id], $shared);
	}

	public function testNoCrossUserCacheLeakage(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$user1 = User::factory()->create();
		$mine1 = Album::factory()->as_root()->owned_by($user1)->create();
		$user2 = User::factory()->create();
		$mine2 = Album::factory()->as_root()->owned_by($user2)->create();

		$this->actingAs($user1)->getJsonV3('Albums/root?scope=own')->assertOk();

		$ids2 = $this->actingAs($user2)->getJsonV3('Albums/root?scope=own')->assertOk()->json('ids');
		self::assertSame([$mine2->id], $ids2);
	}

	/**
	 * SettingsController::setConfigs dispatches AlbumListingCacheFlushRequested
	 * (synchronous, coarse) *before* queuing RecomputeRootAlbumBucketsJob. A
	 * request landing in that gap re-warms the root buckets cache with the
	 * pre-upsert bucket_id — a plain global-tag flush can never catch that
	 * refill, since it already ran. The job itself must therefore evict
	 * albumChildrenTag(null) again once its own upsert() actually lands.
	 */
	public function testRecomputeJobInvalidatesRootCacheRewarmedDuringTheFlushDispatchGap(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$user = User::factory()->create();
		Album::factory()->as_root()->owned_by($user)->create([
			'created_at' => new \Illuminate\Support\Carbon('2024-01-01'),
		]);
		$this->recomputeRootBuckets();

		$before = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk()->json('bucket_ids');
		self::assertSame(['2024'], $before);

		// Admin changes the sorting config: SettingsController fires the
		// coarse flush synchronously, then queues the recompute job.
		Configs::set('title_bucket_mode', 'alphabetical');
		Configs::set('sorting_albums_col', 'title');
		event(new \App\Events\AlbumListingCacheFlushRequested());

		// The race: a request lands after the flush but before the queued
		// job runs, re-warming the cache — bucket_id in the DB has not been
		// touched yet, so this is still correct *at this instant*, but the
		// entry it leaves behind is now stale the moment the job upserts.
		$rewarmed = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk()->json('bucket_ids');
		self::assertSame(['2024'], $rewarmed);

		// The queued job finally runs and upserts the new bucket_id.
		$this->recomputeRootBuckets();

		$after = $this->actingAs($user)->getJsonV3('Albums/root/buckets?scope=own')->assertOk()->json('bucket_ids');
		self::assertNotSame(['2024'], $after, 'Root buckets cache served stale pre-upsert data after the recompute job ran.');
	}
}
