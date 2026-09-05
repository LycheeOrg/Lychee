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

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Services\TitleSplitter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 061 FR-061-05..10/18, S-061-01..11, S-061-31..33.
 *
 * Builds its own isolated fixture per test (rather than editing the shared
 * v2/v3 base fixture) so it stays independent of other v3 test suites.
 */
class AlbumBucketsV3Test extends BaseApiWithDataTest
{
	private function setInstanceDefaults(string $granularity = 'year'): void
	{
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => $granularity]);
	}

	private function recompute(Album $album): void
	{
		(new RecomputeAlbumStatsJob($album->id, propagate_to_parent: false))->handle();
	}

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	// ── Flag gate ─────────────────────────────────────────────────

	public function testFlagOffReturns403RegardlessOfCallerRights(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/buckets");
		$this->assertForbidden($response);
	}

	// ── Grouping per source ──────────────────────────────────────

	public function testGroupsByCreatedAtWithUnknownNeverApplicable(): void
	{
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();

		$c1 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-05-01')]);
		$c2 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2024-05-01')]);
		$this->recompute($c1);
		$this->recompute($c2);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson([
			'bucket_ids' => ['2023', '2024'],
			'counts' => [1, 1],
			'bucketable' => true,
		]);
	}

	public function testGroupsByMinTakenAtWithUndatedChildrenUnderUnknown(): void
	{
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::MIN_TAKEN_AT, OrderSortingType::ASC);
		$parent->save();

		$dated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create(['taken_at' => new Carbon('2022-06-15')]);
		$photo->albums()->attach($dated->id);
		$this->recompute($dated);

		$undated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($undated);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson([
			'bucket_ids' => ['2022', 'unknown'],
			'counts' => [1, 1],
			'bucketable' => true,
		]);
	}

	public function testGroupsByMaxTakenAt(): void
	{
		$this->setInstanceDefaults('month');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::MAX_TAKEN_AT, OrderSortingType::ASC);
		$parent->save();

		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create(['taken_at' => new Carbon('2022-06-15')]);
		$photo->albums()->attach($child->id);
		$this->recompute($child);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2022-06'], 'counts' => [1], 'bucketable' => true]);
	}

	public function testGroupsByTitleDatePrefixWithUnknownAndPlainChronologicalOrder(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'date_prefix']);
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::DESC);
		$parent->save();

		$c1 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->with_title('2024 trip')->create();
		$c2 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->with_title('2020 trip')->create();
		$c3 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->with_title('no date here')->create();
		$this->recompute($c1);
		$this->recompute($c2);
		$this->recompute($c3);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		// Plain ORDER BY bucket_id DESC (the parent's own sort direction),
		// "unknown" always last — never routed through title's own
		// natural-sort (title_base/title_index) semantics.
		$response->assertJson(['bucket_ids' => ['2024', '2020', 'unknown'], 'counts' => [1, 1, 1]]);
	}

	// ── bucketable: false / edge branches ────────────────────────

	public function testOwnerIdSortColumnIsNotBucketable(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		$parent->save();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertExactJson(['bucket_ids' => [], 'counts' => [], 'labels' => [], 'bucketable' => false]);
	}

	public function testTagAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}/buckets");
		$this->assertNotFound($response);
	}

	public function testUnknownAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/AAAAAAAAAAAAAAAAAAAAAAAA/buckets');
		$this->assertNotFound($response);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}/buckets");
		$this->assertForbidden($response);
	}

	public function testZeroChildrenParentReturnsEmptyArrays(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album5->id}/buckets");
		$this->assertOk($response);
		$response->assertExactJson(['bucket_ids' => [], 'counts' => [], 'labels' => [], 'bucketable' => true]);
	}

	public function testDepthTwoSubalbumWorksIdentically(): void
	{
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->children_of($this->subAlbum1)->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();
		$grandchild = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($grandchild);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['counts' => [1], 'bucketable' => true]);
	}

	// ── Labels (T-061-12a) ────────────────────────────────────────

	public function testLabelsMatchConfiguredDateFormatForNonDefaultMonthFormat(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_album_date_format_month')->update(['value' => 'F Y']);
		$this->setInstanceDefaults('month');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2024-03-01')]);
		$this->recompute($child);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2024-03'], 'labels' => ['March 2024']]);
	}

	/**
	 * A bare 4-digit bucket_id ("2020") is ambiguous to PHP's date parser —
	 * Carbon::parse('2020') reads it as the time 20:20 on *today's* date, not
	 * the year 2020, silently returning today's year for every YEAR-granularity
	 * label. The fixture year is deliberately far from "now" so this doesn't
	 * pass by coincidence.
	 */
	public function testYearGranularityLabelsUseTheBucketsOwnYearNotToday(): void
	{
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2005-06-01')]);
		$this->recompute($child);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['2005'], 'labels' => ['2005']]);
	}

	public function testAlphabeticalTitleLabelsAreVerbatim(): void
	{
		DB::table('configs')->where('key', '=', 'title_bucket_mode')->update(['value' => 'alphabetical']);
		DB::table('configs')->where('key', '=', 'title_bucket_prefix_length')->update(['value' => '1']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$parent->save();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->with_title('Zebras')->create();
		$child->title_base = TitleSplitter::split($child->title)->base;
		$child->save();
		$this->recompute($child);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['z'], 'labels' => ['z']]);
	}

	public function testUnknownEntryLabelIsLiteralStringNotDateParsed(): void
	{
		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::MIN_TAKEN_AT, OrderSortingType::ASC);
		$parent->save();
		$undated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($undated);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($response);
		$response->assertJson(['bucket_ids' => ['unknown'], 'labels' => ['unknown']]);
	}

	// ── Managed cache (I6, F-061-08, S-061-17..19) ───────────────

	/** @param string[] $tables */
	private function countTableQueries(\Closure $callback, array $tables): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$callback();
		$pattern = '/\b(' . implode('|', $tables) . ')\b/';
		$count = count(array_filter(
			DB::getQueryLog(),
			fn (array $q) => preg_match($pattern, str_replace(['"', '`'], '', $q['query'])) === 1
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		return $count;
	}

	/**
	 * S-061-17: a cache hit must skip the `GROUP BY bucket_id` aggregation
	 * query itself on the second request. The request's own album
	 * resolution/authorization (`GetAlbumBucketsRequest`) still queries
	 * `albums`/`base_albums`/`access_permissions` every request regardless
	 * of the cache — that cost is orthogonal to this endpoint's own
	 * `ManagedCacheService::rememberIf()` wrapping (which only wraps the
	 * aggregation itself), so this asserts the aggregation query
	 * specifically, not a zero-query count for those tables.
	 */
	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$aggregation_queries = array_filter($log, fn (array $q) => preg_match('/group by/i', $q['query']) === 1);
		self::assertCount(0, $aggregation_queries, 'A cache hit must not re-run the GROUP BY aggregation query.');
	}

	public function testCacheInvalidatedOnChildAdded(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->setInstanceDefaults('year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk()->json('counts');
		self::assertSame([1], $before);

		$create_response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $parent->id,
			'title' => 'Feature-061-new-child',
		]);
		self::assertSame(200, $create_response->getStatusCode());
		$new_album_id = $create_response->getOriginalContent();
		$this->recompute(Album::findOrFail($new_album_id));

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk()->json('counts');
		self::assertSame([2], $after);
	}

	/**
	 * S-061-19: two identities with different visibility into the same
	 * parent never share a cache entry — a second, different caller's
	 * request for the same `album_id` is never served from the first
	 * caller's cached entry (the cache key is `(album_id, user)`, not just
	 * `album_id`).
	 */
	public function testNoCrossIdentityCacheLeakage(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/buckets")->assertOk();

		$admin_call_count = $this->countTableQueries(
			fn () => $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}/buckets")->assertOk(),
			['albums', 'base_albums', 'access_permissions']
		);

		self::assertGreaterThan(0, $admin_call_count, 'A different caller must not be served from another identity\'s cached entry.');
	}

	/**
	 * Fixed CodeRabbit finding on PR #4680: this endpoint's cache never
	 * registered userTag($user_id) as an actual invalidation tag - a caller
	 * who gains bucket-relevant visibility via a group-based grant would
	 * keep seeing the pre-change bucket counts until TTL expiry.
	 */
	public function testCacheInvalidatedOnGroupMembershipChange(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($parent)->create();

		$hidden_child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-01-01')]);
		$this->recompute($hidden_child);
		AccessPermission::factory()->for_user_group($this->group1)->for_album($hidden_child)->visible()->create();

		$before = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($before);
		self::assertSame([], $before->json('bucket_ids'));

		$add_response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->postJson('UserGroups/Users', [
			'group_id' => $this->group1->id,
			'user_id' => $this->userNoUpload->id,
			'role' => 'member',
		]);
		$this->assertCreated($add_response);

		$this->userNoUpload->unsetRelation('user_groups')->refresh();

		$after = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($after);
		self::assertSame([1], $after->json('counts'));
	}
}
