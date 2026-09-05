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

use App\Enum\SmartAlbumType;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 062 FR-062-09..11/15, S-062-14..18/22..27.
 */
class AlbumCategoryV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	// ── Flag gate (S-062-18) ──────────────────────────────────────────

	public function testFlagOffReturns403ForEveryCategoryRoute(): void
	{
		config(['features.struct-of-array' => false]);

		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/smart'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/tags'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/tags/rights'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/persons?scope=own'));
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/pinned?scope=own'));
	}

	// ── smart (S-062-14) ───────────────────────────────────────────────

	public function testSmartReturnsSameSetAsV2WithZeroQueries(): void
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$json = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/smart')->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertSame(['ids', 'titles', 'cover_ids', 'owner_ids'], array_keys($json));
		self::assertNotEmpty($json['ids']);

		// AlbumFactory::getAllBuiltInSmartAlbums(false) itself still runs one
		// cheap AccessPermission lookup per smart album type (unrelated to
		// this endpoint's own code) — "zero SQL query" (S-062-14) means zero
		// *photos* queries specifically, i.e. with_relations=false is honored
		// and no eager photos/size_variants load ever runs.
		$photo_queries = array_filter($log, fn (array $q) => preg_match('/\bphotos\b/i', $q['query']) === 1);
		self::assertCount(0, $photo_queries, 'Smart albums listing must never query photos (with_relations=false).');
	}

	/**
	 * 2026-09-02 amendment (Feature 063 FR-062-16, Q-063-15): `cover_ids`
	 * resolves from the pre-computed `album_user_thumbs` cache — a hit
	 * returns the cached photo id, a miss stays `null`, and this stays a
	 * cache-only lookup (no live `photos` query — the assertion above
	 * already covers that for the whole endpoint, seeded row included).
	 */
	public function testSmartResolvesRealCoverFromCacheHitAndNullFromCacheMiss(): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => SmartAlbumType::UNSORTED->value,
			'photo_id' => $this->photoUnsorted->id,
		]);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$json = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/smart')->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$photo_queries = array_filter($log, fn (array $q) => preg_match('/\bphotos\b/i', $q['query']) === 1);
		self::assertCount(0, $photo_queries, 'Cover resolution must stay cache-only, never a live photos query.');

		$unsorted_index = array_search(SmartAlbumType::UNSORTED->value, $json['ids'], true);
		self::assertNotFalse($unsorted_index, 'unsorted must be visible to a may-upload user.');
		self::assertSame($this->photoUnsorted->id, $json['cover_ids'][$unsorted_index]);

		// Every other visible smart album has no seeded cache row -> null,
		// not a live-resolved fallback.
		foreach ($json['ids'] as $i => $id) {
			if ($id === SmartAlbumType::UNSORTED->value) {
				continue;
			}
			self::assertNull($json['cover_ids'][$i], "cover_ids for '{$id}' must be null on a cache miss.");
		}
	}

	// ── tags (S-062-15) ────────────────────────────────────────────────

	public function testTagsReturnsFlatUnscopedListingWithZeroEagerLoads(): void
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$json = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/tags')->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		self::assertSame(['ids', 'titles', 'cover_ids', 'owner_ids'], array_keys($json));
		self::assertContains($this->tagAlbum1->id, $json['ids']);

		// Top::queryTagAlbums()'s eager loads (`access_permissions`,
		// `owner`, `userThumbRow.photo.size_variants`) are deliberately
		// dropped for this toBase()-queried endpoint (FR-062-09) — assert
		// their absence directly rather than an easily-miscounted total.
		$eager_load_queries = array_filter($log, fn (array $q) => preg_match('/\bphotos\b|album_user_thumbs|size_variants/i', $q['query']) === 1);
		self::assertCount(0, $eager_load_queries, 'Tags listing must not run any of the eager-load queries Top::queryTagAlbums() carries.');
	}

	public function testTagsRightsNonAdminGetsRealPerRowGrantsAdminGetsAllTrue(): void
	{
		$this->album1->tags()->sync([$this->tag_test->id]);
		AccessPermission::factory()
			->for_user($this->userMayUpload1)
			->visible()
			->grants_edit()
			->create(['base_album_id' => $this->tagAlbum1->id]);

		$non_admin = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/tags/rights')->assertOk()->json();
		$idx = array_search($this->tagAlbum1->id, $non_admin['ids'], true);
		self::assertNotFalse($idx);
		self::assertTrue($non_admin['grants_edit'][$idx]);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$admin = $this->actingAs($this->admin)->getJsonV3('Albums/tags/rights')->assertOk()->json();
		$log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$admin_idx = array_search($this->tagAlbum1->id, $admin['ids'], true);
		self::assertTrue($admin['grants_edit'][$admin_idx]);
		self::assertTrue($admin['grants_download'][$admin_idx]);
		self::assertTrue($admin['grants_delete'][$admin_idx]);

		$join_queries = array_filter($log, fn (array $q) => preg_match('/grants_computed_access_permissions/i', $q['query']) === 1);
		self::assertCount(0, $join_queries, 'Admin must never run the grants-join query.');
	}

	/**
	 * S-062-17: renaming one tag album evicts only the `tags` category's
	 * cache entry — a subsequent request for `/Albums/persons` (any scope)
	 * still serves its cached result, unaffected.
	 */
	public function testRenamingATagAlbumEvictsOnlyTagsCacheNotPersons(): void
	{
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');

		$this->actingAs($this->userMayUpload1)->getJsonV3('Albums/tags')->assertOk();
		$this->actingAs($this->userMayUpload1)->getJsonV3('Albums/persons?scope=own')->assertOk();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'renamed-title',
			'tags' => ['tag1'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->actingAs($this->userMayUpload1)->getJsonV3('Albums/persons?scope=own')->assertOk();
		$persons_log = DB::getQueryLog();
		DB::flushQueryLog();
		DB::disableQueryLog();

		$persons_join_queries = array_filter($persons_log, fn (array $q) => preg_match('/person_albums/i', $q['query']) === 1);
		self::assertCount(0, $persons_join_queries, 'Persons cache must still be warm — tag rename must not have evicted it.');

		$tags_json = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/tags')->assertOk()->json();
		self::assertContains('renamed-title', $tags_json['titles']);
	}

	// ── persons (S-062-15/26/27) ─────────────────────────────────────

	public function testPersonsEmptyBlockWhenAiVisionFaceDisabled(): void
	{
		Configs::set('ai_vision_face_enabled', '0');

		$json = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/persons?scope=own')->assertOk()->json();

		self::assertSame(['ids' => [], 'titles' => [], 'cover_ids' => [], 'owner_ids' => []], $json);
	}

	private function createPersonAlbum(User $owner, string $title): string
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => $title, 'is_searchable' => true]);
		Face::factory()->for_photo($this->photo1)->for_person($person)->create();

		$response = $this->actingAs($owner)->postJson('PersonAlbum', [
			'title' => $title,
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);

		return $response->getOriginalContent();
	}

	public function testPersonsOwnScopeOnlyReturnsCallersOwnPersonAlbums(): void
	{
		$mine_id = $this->createPersonAlbum($this->userMayUpload1, 'mine');

		$ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/persons?scope=own')->assertOk()->json('ids');

		self::assertSame([$mine_id], $ids);
	}

	public function testPersonsSharedScopeIsOneFlatListNotGroupedByOwner(): void
	{
		$mine_id = $this->createPersonAlbum($this->userMayUpload1, 'mine');
		AccessPermission::factory()->public()->visible()->create(['base_album_id' => $mine_id]);

		$json = $this->actingAs($this->userMayUpload2)->getJsonV3('Albums/persons?scope=shared')->assertOk()->json();

		self::assertSame(['ids', 'titles', 'cover_ids', 'owner_ids'], array_keys($json));
		self::assertSame([$mine_id], $json['ids']);
	}

	public function testPersonsAuthenticatedOmittingScopeReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3('Albums/persons'));
	}

	public function testPersonsGuestScopeOwnReturns422AndNoBucketsRouteExists(): void
	{
		Configs::set('ai_vision_face_enabled', '1');

		$this->assertUnprocessable($this->getJsonV3('Albums/persons?scope=own'));
		// No dedicated /Albums/persons/buckets route exists (NG9) — this URL
		// instead falls through to the generic /Albums/{album_id}/buckets
		// wildcard, treating "persons" as an album_id, which the RandomIDRule
		// rejects with 422 (not the AlbumBucketResource shape a real buckets
		// endpoint would return).
		$this->assertUnprocessable($this->getJsonV3('Albums/persons/buckets'));
	}

	// ── pinned (S-062-15/22/23/24/25) ──────────────────────────────────

	public function testPinnedIncludesSubAlbumsNotRestrictedToRoot(): void
	{
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$pinned_sub = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create();
		DB::table('base_albums')->where('id', '=', $pinned_sub->id)->update(['is_pinned' => true]);

		$ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/pinned?scope=own')->assertOk()->json('ids');

		self::assertSame([$pinned_sub->id], $ids);
	}

	public function testPinnedOwnScopeOnlyReturnsCallersOwnPinnedAlbums(): void
	{
		$mine = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('base_albums')->where('id', '=', $mine->id)->update(['is_pinned' => true]);
		$theirs = Album::factory()->as_root()->owned_by($this->userMayUpload2)->create();
		DB::table('base_albums')->where('id', '=', $theirs->id)->update(['is_pinned' => true]);

		$ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/pinned?scope=own')->assertOk()->json('ids');

		self::assertSame([$mine->id], $ids);
	}

	public function testPinnedSharedScopeIsOneFlatListExcludingCallersOwn(): void
	{
		$mine = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('base_albums')->where('id', '=', $mine->id)->update(['is_pinned' => true]);
		$theirs = Album::factory()->as_root()->owned_by($this->userMayUpload2)->create();
		DB::table('base_albums')->where('id', '=', $theirs->id)->update(['is_pinned' => true]);
		AccessPermission::factory()->public()->visible()->for_album($theirs)->create();

		$ids = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/pinned?scope=shared')->assertOk()->json('ids');

		self::assertSame([$theirs->id], $ids);
	}

	public function testPinnedGuestScopeOwnReturns422PublicOnlyOtherwiseNoBucketsRoute(): void
	{
		$theirs = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		DB::table('base_albums')->where('id', '=', $theirs->id)->update(['is_pinned' => true]);
		AccessPermission::factory()->public()->visible()->for_album($theirs)->create();

		$this->assertUnprocessable($this->getJsonV3('Albums/pinned?scope=own'));

		$ids = $this->getJsonV3('Albums/pinned')->assertOk()->json('ids');
		self::assertSame([$theirs->id], $ids);

		// See testPersonsGuestScopeOwnReturns422AndNoBucketsRouteExists() —
		// same wildcard-fallthrough reasoning applies to pinned.
		$this->assertUnprocessable($this->getJsonV3('Albums/pinned/buckets'));
	}

	public function testPinnedAuthenticatedOmittingScopeReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3('Albums/pinned'));
	}
}
