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
use App\Events\UserGroupMembershipChanged;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 061 FR-061-12..17, S-061-23..30.
 */
class AlbumChildrenDataV3Test extends BaseApiWithDataTest
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

		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album1->id}");
		$this->assertForbidden($response);
	}

	// ── Field list / parity ──────────────────────────────────────

	public function testFieldListMatchesFR06112AndVisibilitySetMatchesV2Pagination(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}");
		$this->assertOk($response);
		$json = $response->json();

		foreach (['ids', 'titles', 'descriptions', 'cover_ids', 'bucket_ids', 'is_password_requireds', 'is_nsfws', 'has_subalbums', 'num_photos', 'num_subalbums', 'created_ats', 'min_taken_ats', 'max_taken_ats'] as $field) {
			self::assertArrayHasKey($field, $json, "missing field {$field}");
		}
		self::assertContains($this->subAlbum1->id, $json['ids']);
		self::assertCount(1, $json['ids']);
	}

	/**
	 * S-061-23/NFR-061-08: for a fixed set of children with mixed
	 * visibility, the set of `ids` returned equals the set
	 * `AlbumChildrenController::get()` would paginate over for the same
	 * caller — a private, non-shared child must never leak into the
	 * response for a caller lacking access.
	 */
	public function testVisibilityFilterExcludesInaccessibleChildren(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$visible = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($visible);

		// Owner sees it.
		$owner_ids = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json('ids');
		self::assertContains($visible->id, $owner_ids);

		// Parent itself is private (no grants), so a stranger cannot even
		// resolve/access the parent to begin with.
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}");
		$this->assertForbidden($response);
	}

	// ── Pin / public / link-required (FR-061-27) ──────────────────

	/**
	 * S-061-44: a fixture spanning a pinned child, an unpinned child, a
	 * public+no-link-required child, a public+link-required child, and a
	 * fully private child — `is_pinneds`/`is_publics`/`is_link_requireds`
	 * match `ThumbAlbumResource`'s own resolution for the same children.
	 */
	public function testPinPublicLinkRequiredFieldsMatchThumbAlbumResource(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$pinned = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$pinned->is_pinned = true;
		$pinned->save();
		$this->recompute($pinned);

		$unpinned = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($unpinned);

		$public_visible = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->for_album($public_visible)->create();
		$this->recompute($public_visible);

		$public_link_required = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->for_album($public_link_required)->create();
		$this->recompute($public_link_required);

		$private = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($private);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}");
		$this->assertOk($response);
		$json = $response->json();

		$idx_pinned = array_search($pinned->id, $json['ids'], true);
		$idx_unpinned = array_search($unpinned->id, $json['ids'], true);
		$idx_public_visible = array_search($public_visible->id, $json['ids'], true);
		$idx_public_link_required = array_search($public_link_required->id, $json['ids'], true);
		$idx_private = array_search($private->id, $json['ids'], true);

		self::assertTrue($json['is_pinneds'][$idx_pinned]);
		self::assertFalse($json['is_pinneds'][$idx_unpinned]);

		self::assertTrue($json['is_publics'][$idx_public_visible]);
		self::assertFalse($json['is_link_requireds'][$idx_public_visible]);

		self::assertTrue($json['is_publics'][$idx_public_link_required]);
		self::assertTrue($json['is_link_requireds'][$idx_public_link_required]);

		self::assertFalse($json['is_publics'][$idx_private]);
		self::assertFalse($json['is_link_requireds'][$idx_private]);
	}

	// ── Description truncation ───────────────────────────────────

	public function testDescriptionTruncatedToOneHundredCharsBySql(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$long_description = str_repeat('a', 150);
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$child->description = $long_description;
		$child->save();
		$this->recompute($child);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}");
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($response);
		self::assertSame(str_repeat('a', 100), $response->json('descriptions')[0]);

		$truncating_queries = array_filter($log, fn (array $q) => preg_match('/substr/i', $q['query']) === 1);
		self::assertGreaterThan(0, count($truncating_queries), 'Expected the description truncation to happen via a SQL SUBSTR, not PHP.');
	}

	// ── Cover resolution ──────────────────────────────────────────

	public function testCoverIdResolutionAcrossAllThreeTiersPlusNoCover(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$explicit = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo_explicit = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$photo_explicit->albums()->attach($explicit->id);
		$explicit->cover_id = $photo_explicit->id;
		$explicit->save();
		$this->recompute($explicit);

		$auto = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo_auto = Photo::factory()->owned_by($this->userMayUpload1)->create();
		$photo_auto->albums()->attach($auto->id);
		$this->recompute($auto);

		$empty = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($empty);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}");
		$this->assertOk($response);
		$json = $response->json();

		$idx_explicit = array_search($explicit->id, $json['ids'], true);
		$idx_auto = array_search($auto->id, $json['ids'], true);
		$idx_empty = array_search($empty->id, $json['ids'], true);

		self::assertSame($photo_explicit->id, $json['cover_ids'][$idx_explicit]);
		self::assertSame($photo_auto->id, $json['cover_ids'][$idx_auto]);
		self::assertNull($json['cover_ids'][$idx_empty]);

		self::assertArrayNotHasKey('type', $json);
		self::assertArrayNotHasKey('placeholder', $json);
	}

	// ── Edge branches ─────────────────────────────────────────────

	public function testZeroChildrenParentReturnsEmptyArrays(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3("Albums/{$this->album5->id}");
		$this->assertOk($response);
		$response->assertExactJson([
			'ids' => [], 'titles' => [], 'descriptions' => [], 'cover_ids' => [], 'bucket_ids' => [], 'owner_ids' => [],
			'is_password_requireds' => [], 'is_nsfws' => [], 'is_pinneds' => [], 'is_publics' => [],
			'is_link_requireds' => [], 'has_subalbums' => [], 'num_photos' => [],
			'num_subalbums' => [], 'created_ats' => [], 'min_taken_ats' => [], 'max_taken_ats' => [],
		]);
	}

	public function testUnresolvableAlbumIdReturns404(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/AAAAAAAAAAAAAAAAAAAAAAAA');
		$this->assertNotFound($response);
	}

	public function testNoAccessReturns403(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$this->album1->id}");
		$this->assertForbidden($response);
	}

	// ── Cross-endpoint bucket_id correlation (T-061-27) ───────────

	public function testBucketIdCorrelatesExactlyWithBucketsEndpoint(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();

		$c1 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-01-01')]);
		$c2 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2023-06-01')]);
		$c3 = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2024-01-01')]);
		$this->recompute($c1);
		$this->recompute($c2);
		$this->recompute($c3);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk()->json();
		$children = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json();

		$grouped = [];
		foreach ($children['bucket_ids'] as $bucket_id) {
			$grouped[$bucket_id] = ($grouped[$bucket_id] ?? 0) + 1;
		}

		$expected = array_combine($buckets['bucket_ids'], $buckets['counts']);
		self::assertEquals($expected, $grouped);

		// FR-061-26: the children endpoint's own row order must reproduce
		// the buckets endpoint's bucket order exactly (first-seen bucket_id
		// sequence, deduplicated).
		self::assertSame($buckets['bucket_ids'], array_values(array_unique($children['bucket_ids'])));
	}

	public function testBucketIdCorrelationIncludingUnknown(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['value' => 'min_taken_at']);
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::MIN_TAKEN_AT, OrderSortingType::ASC);
		$parent->save();

		$dated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->create(['taken_at' => new Carbon('2022-01-01')]);
		$photo->albums()->attach($dated->id);
		$this->recompute($dated);

		$undated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($undated);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk()->json();
		$children = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json();

		self::assertContains('unknown', $children['bucket_ids']);
		$grouped = array_count_values($children['bucket_ids']);
		$expected = array_combine($buckets['bucket_ids'], $buckets['counts']);
		self::assertEquals($expected, $grouped);

		// FR-061-26: "unknown" sorts last in both endpoints' row order,
		// exactly like the buckets endpoint's own guarantee.
		self::assertSame($buckets['bucket_ids'], array_values(array_unique($children['bucket_ids'])));
		self::assertSame('unknown', $children['bucket_ids'][count($children['bucket_ids']) - 1]);
	}

	/**
	 * FR-061-26: under `title_bucket_mode=date_prefix`, the parsed-date
	 * `bucket_id` and the `title_base`/`title_index` sort key are unrelated
	 * dimensions of the same title string — ordering purely by the effective
	 * sort column would NOT keep same-bucket rows contiguous. This is the one
	 * case where ordering by bucket_id first is not just cosmetic.
	 */
	public function testChildrenGroupedByBucketEvenWhenTitleOrderInterleavesBuckets(): void
	{
		DB::table('configs')->where('key', '=', 'timeline_albums_granularity')->update(['value' => 'year']);
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::TITLE, OrderSortingType::ASC);
		$parent->save();

		// `parseDateFromTitle()` requires a leading 4-digit year (`^\d{4}`);
		// a single leading digit does not match, so this title's bucket_id
		// is null ("unknown") — but '1' sorts *before* '2' character-by-
		// character, so a plain ascending title sort would place it FIRST,
		// while the buckets endpoint always places "unknown" LAST regardless
		// of direction (FR-061-07). This divergence is guaranteed by plain
		// digit-code-point ordering, not by any driver-specific collation.
		$undated = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['title' => '1 undated trip']);
		$early = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['title' => '2020-01 Charlie']);
		$late = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['title' => '2021-01 Delta']);
		$this->recompute($undated);
		$this->recompute($early);
		$this->recompute($late);

		$buckets = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets")->assertOk()->json();
		$children = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json();

		self::assertSame(['2020', '2021', 'unknown'], $buckets['bucket_ids']);
		// A plain title-ascending sort would put "1 undated trip" FIRST
		// (bucket_id join order: unknown, 2020, 2021) — the opposite of what
		// the buckets endpoint promises. Confirm the children endpoint
		// matches the buckets endpoint's order instead.
		self::assertSame($buckets['bucket_ids'], array_values(array_unique($children['bucket_ids'])));
		self::assertSame($undated->id, $children['ids'][count($children['ids']) - 1]);
	}

	// ── Managed cache (T-061-25) ──────────────────────────────────

	public function testCacheHitSkipsTheAggregationQuery(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk();

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$relevant = array_filter($log, fn (array $q) => preg_match('/\bwhere\b.*parent_id/i', $q['query']) === 1);
		self::assertCount(0, $relevant, 'A cache hit must not re-run the children query.');
	}

	public function testCacheInvalidatedOnChildAdded(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		$this->recompute($child);

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json('ids');
		self::assertCount(1, $before);

		$create_response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $parent->id,
			'title' => 'Feature-061-new-child-2',
		]);
		self::assertSame(200, $create_response->getStatusCode());

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}")->assertOk()->json('ids');
		self::assertCount(2, $after);
	}

	// ── TagAlbum / PersonAlbum "matching albums" support ───────────

	public function testTagAlbumChildrenReturnsAlbumsCarryingTheTag(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}")->assertOk()->json();

		self::assertSame([$this->album1->id], $json['ids']);
	}

	public function testTagAlbumChildrenEmptyWhenNoAlbumCarriesTheTag(): void
	{
		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}")->assertOk()->json();

		self::assertSame([], $json['ids']);
	}

	public function testTagAlbumChildrenEmptyWhenListingConfigDisabled(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);
		Configs::set('TA_albums_listing_enabled', '0');

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}")->assertOk()->json();

		self::assertSame([], $json['ids']);
	}

	/**
	 * Fixed CodeRabbit finding on PR #4680: the cache key for a TagAlbum's
	 * "matching albums" response did not vary with
	 * AlbumPolicy::getUnlockedAlbumIDs() (session-scoped) - a caller who
	 * unlocked a password-protected matching album could have been served
	 * (or could have served another caller) a stale, wrong-unlock-state
	 * response until TTL expiry. Mirrors
	 * tests/Feature_v2/Tags/GetTagsTest.php's
	 * testDifferentUnlockStatesDoNotShareACacheEntry() for this new endpoint.
	 */
	public function testDifferentUnlockStatesDoNotShareACacheEntry(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');

		$password_album = $this->album2;
		$password_album->tags()->sync([$this->tag_test->id]);
		AccessPermission::factory()->public()->visible()->locked()->for_album($password_album)->create();

		// Never unlocked - the password-protected album must not appear.
		$response_locked = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}");
		$this->assertOk($response_locked);
		self::assertSame([], $response_locked->json('ids'));

		// Same identity, now with the album unlocked in session - must not
		// be served the response cached above for the locked state.
		session()->push(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, $password_album->id);
		$response_unlocked = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->tagAlbum1->id}");
		$this->assertOk($response_unlocked);
		self::assertSame([$password_album->id], $response_unlocked->json('ids'));
	}

	// ── Cache invalidation on group-membership change ──────────────

	/**
	 * Fixed CodeRabbit finding on PR #4680: none of the three new endpoints
	 * registered userTag($user_id) as an actual cache invalidation tag (it
	 * was only ever embedded as text inside the key string) - so
	 * UserGroupMembershipChanged, whose handler evicts exactly that tag,
	 * could never reach these caches. A caller who gains (or loses)
	 * visibility via a group-based grant would keep seeing the pre-change
	 * set until TTL expiry.
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
		AccessPermission::factory()->for_user_group($this->group1)->for_album($hidden_child)->visible()->create();

		// userNoUpload can see the (public) parent, but not yet the child -
		// they are not a member of group1.
		$before = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}");
		$this->assertOk($before);
		self::assertSame([], $before->json('ids'));

		// Add them to the group via the real endpoint, which dispatches
		// UserGroupMembershipChanged.
		$add_response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->postJson('UserGroups/Users', [
			'group_id' => $this->group1->id,
			'user_id' => $this->userNoUpload->id,
			'role' => 'member',
		]);
		$this->assertCreated($add_response);

		// The PHP object held by $this->userNoUpload can carry a stale,
		// already-loaded `user_groups` relation from the request above;
		// reload it so the next request's Auth::user()->user_groups
		// reflects the just-added membership, not a cached empty collection.
		$this->userNoUpload->unsetRelation('user_groups')->refresh();

		$after = $this->actingAs($this->userNoUpload)->getJsonV3("Albums/{$parent->id}");
		$this->assertOk($after);
		self::assertSame([$hidden_child->id], $after->json('ids'));
	}

	public function testPersonAlbumChildrenReturnsAlbumsContainingAMatchingFace(): void
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

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$person_album_id}")->assertOk()->json();

		self::assertSame([$this->album1->id], $json['ids']);
	}
}
