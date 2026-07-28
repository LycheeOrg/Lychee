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

namespace Tests\Feature_v2\Caching;

use App\Events\PhotoAdded;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Proves the full wiring end-to-end: real controller call -> real event ->
 * real registered listener (EventServiceProvider) -> ManagedCacheService tag
 * eviction, with no faking. Complements the more granular unit tests for the
 * service and listeners in isolation.
 */
class ManagedCacheServiceWiringTest extends BaseApiWithDataTest
{
	private ManagedCacheService $cache_service;

	public function setUp(): void
	{
		parent::setUp();
		Configs::set('managed_cache_enabled', '1');
		$this->cache_service = new ManagedCacheService(new ConfigManager());
	}

	public function testMovingAlbumEvictsOldAndNewParentTagsThroughTheRealEventBus(): void
	{
		$old_parent_key = 'wiring-test:children:' . $this->album1->id;
		$new_parent_key = 'wiring-test:children:' . $this->album5->id;
		$this->cache_service->remember($old_parent_key, ['album:' . $this->album1->id], 60, fn () => 'cached-children-of-album1');
		$this->cache_service->remember($new_parent_key, ['album:' . $this->album5->id], 60, fn () => 'cached-children-of-album5');

		$response = $this->actingAs($this->admin)->postJson('Album::move', [
			'album_id' => $this->album5->id,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);

		self::assertNull(Cache::get($old_parent_key));
		self::assertNull(Cache::get($new_parent_key));
	}

	public function testUserGroupMembershipChangeEvictsTheUsersTagThroughTheRealEventBus(): void
	{
		$this->requireSe();

		$key = 'wiring-test:user:' . $this->userNoUpload->id;
		$this->cache_service->remember($key, ['user:' . $this->userNoUpload->id], 60, fn () => 'cached-permissions');

		$response = $this->actingAs($this->userWithGroupAdmin)->postJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->assertCreated($response);

		self::assertNull(Cache::get($key));

		$this->resetSe();
	}

	public function testGettingAlbumPhotosTwiceHitsCacheOnSecondCallThroughTheRealEndpoint(): void
	{
		$first = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($first);

		DB::enableQueryLog();
		$second = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$photo_queries = array_filter(DB::getQueryLog(), fn ($q) => str_contains(strtolower($q['query']), 'from "photos"'));
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($second);
		self::assertCount(0, $photo_queries, 'A managed-cache hit must not re-execute the photo query, even through the real endpoint.');
	}

	public function testAddingAPhotoInvalidatesThePreviouslyCachedPhotoListingThroughTheRealEventBus(): void
	{
		$first = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($first);
		$first->assertJsonPath('total', 2); // photo1 + photo1b, per BaseApiWithDataTest fixtures

		$new_photo = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		PhotoAdded::dispatch($new_photo->id);

		$second = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($second);
		$second->assertJsonPath('total', 3);
	}

	/**
	 * S-052-08b: a child hidden from a user at write time must become visible
	 * on the next read once that user is granted access, even though the
	 * cached (empty-for-that-child) listing never contained the child's own
	 * tag — closed by evicting the *parent's* tag whenever any child's
	 * `AccessPermissionChanged` fires (FR-052-06).
	 */
	public function testNewlyVisibleChildInvalidatesParentsCachedChildrenListThroughTheRealEventBus(): void
	{
		// userMayUpload2 can see album1 itself (perm1, BaseApiWithDataTest fixtures) but subAlbum1
		// has no permission of its own (permissions aren't automatically inherited without an
		// explicit Sharing::propagate call) — so it's initially absent from the children listing.
		$first = $this->actingAs($this->userMayUpload2)->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($first);
		$first->assertJsonPath('total', 0);

		$grant = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload2->id],
			'group_ids' => [],
			'album_ids' => [$this->subAlbum1->id],
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
		]);
		$this->assertOk($grant);

		$second = $this->actingAs($this->userMayUpload2)->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($second);
		$second->assertJsonPath('total', 1);
		$second->assertJsonPath('data.0.id', $this->subAlbum1->id);
	}

	/**
	 * S-052-11: a guest (unauthenticated) caller uses a fixed 'guest' cache-key
	 * segment — cacheable like any other caller, but never subject to
	 * `UserGroupMembershipChanged` eviction since it isn't tied to a real user id.
	 */
	public function testGuestCachedListingHitsCacheAndSurvivesAnUnrelatedUserGroupChange(): void
	{
		// subAlbum4 is public (BaseApiWithDataTest fixtures), so guests can see it under album4.
		// Filtering on `parent_id` (not just `from "albums"`) specifically isolates the cached
		// *children* query from the route's own incidental, uncached parent-album resolution
		// (e.g. via the `login_required:album` middleware), which legitimately queries `albums`
		// by `id` on every request regardless of this feature's caching.
		$is_children_query = fn ($q) => str_contains(strtolower($q['query']), 'from "albums"') && str_contains(strtolower($q['query']), 'parent_id');

		$first = $this->getJsonWithData('Album::albums', ['album_id' => $this->album4->id]);
		$this->assertOk($first);
		$first->assertJsonPath('total', 1);

		DB::enableQueryLog();
		$second = $this->getJsonWithData('Album::albums', ['album_id' => $this->album4->id]);
		$children_queries = array_filter(DB::getQueryLog(), $is_children_query);
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($second);
		self::assertCount(0, $children_queries, 'Guest cache hit must not re-execute the children query.');

		// A completely unrelated user-group membership change must not evict the guest-scoped entry.
		$this->requireSe();
		$this->actingAs($this->userWithGroupAdmin)->postJson('/UserGroups/Users', [
			'user_id' => $this->userNoUpload->id,
			'group_id' => $this->group1->id,
		]);
		$this->resetSe();
		// actingAs() leaves the guard authenticated for subsequent calls — explicitly log out so
		// the next request is a genuine guest request again, matching the first two calls above.
		$this->app['auth']->forgetGuards();

		DB::enableQueryLog();
		$third = $this->getJsonWithData('Album::albums', ['album_id' => $this->album4->id]);
		$children_queries_after = array_filter(DB::getQueryLog(), $is_children_query);
		DB::flushQueryLog();
		DB::disableQueryLog();

		$this->assertOk($third);
		self::assertCount(0, $children_queries_after, 'Unrelated user-group change must not evict the guest-scoped cache entry.');
	}
}
