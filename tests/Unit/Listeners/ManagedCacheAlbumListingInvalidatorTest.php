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

namespace Tests\Unit\Listeners;

use App\DTO\AlbumSortingCriterion;
use App\Enum\AlbumListingScope;
use App\Events\AccessPermissionChanged;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumComputedDataUpdated;
use App\Events\AlbumDeleted;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\AlbumTagsChanged;
use App\Events\BaseAlbumRemoved;
use App\Events\PersonAlbumSaved;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoWillBeDeleted;
use App\Events\TagAlbumSaved;
use App\Listeners\ManagedCacheAlbumListingInvalidator;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheAlbumListingInvalidatorTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ManagedCacheService $cache_service;
	private CacheKeyProvider $cache_key_provider;
	private ManagedCacheAlbumListingInvalidator $listener;

	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->with('managed_cache_enabled')->andReturn(true);

		$this->cache_service = new ManagedCacheService($config_manager);
		$this->cache_key_provider = new CacheKeyProvider();
		$this->listener = new ManagedCacheAlbumListingInvalidator($this->cache_service, $this->cache_key_provider);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	/** @param string[] $tags */
	private function seedCache(string $key, array $tags): void
	{
		$this->cache_service->remember($key, $tags, fn () => 'value', ttl: 60);
	}

	private function assertEvicted(string $key): void
	{
		self::assertNull(Cache::get($key), "expected key [$key] to have been evicted");
	}

	private function assertNotEvicted(string $key): void
	{
		self::assertNotNull(Cache::get($key), "expected key [$key] to still be cached");
	}

	// ── AlbumSaved ──────────────────────────────────────────────

	public function testAlbumSavedEvictsOwnParentAndRootAndPinnedTags(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$album = Album::factory()->children_of($parent)->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($album->id)]);
		$this->seedCache('k:parent', [$this->cache_key_provider->albumChildrenTag($parent->id)]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);
		$this->seedCache('k:global', [$this->cache_key_provider->albumListingGlobalTag()]);

		$this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id]));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:parent');
		$this->assertEvicted('k:root');
		$this->assertEvicted('k:pinned');
		$this->assertNotEvicted('k:tag-albums');
		$this->assertNotEvicted('k:person-albums');
		$this->assertNotEvicted('k:global');
	}

	public function testAlbumSavedForRootAlbumEvictsRootTagOnce(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);

		$this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id]));

		$this->assertEvicted('k:root');
	}

	// ── AlbumDeleted ────────────────────────────────────────────

	public function testAlbumDeletedEvictsParentAndRootAndPinnedTags(): void
	{
		$this->seedCache('k:parent', [$this->cache_key_provider->albumChildrenTag('some-parent-id')]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);

		$this->listener->handleAlbumDeleted(new AlbumDeleted('some-parent-id'));

		$this->assertEvicted('k:parent');
		$this->assertEvicted('k:root');
		$this->assertEvicted('k:pinned');
		$this->assertNotEvicted('k:tag-albums');
	}

	public function testAlbumDeletedWithNullParentEvictsRootTag(): void
	{
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);

		$this->listener->handleAlbumDeleted(new AlbumDeleted(null));

		$this->assertEvicted('k:root');
	}

	// ── AlbumChildrenChanged ────────────────────────────────────

	public function testAlbumChildrenChangedEvictsOnlyItsOwnParentTag(): void
	{
		$this->seedCache('k:parent', [$this->cache_key_provider->albumChildrenTag('the-parent-id')]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);

		$this->listener->handleAlbumChildrenChanged(new AlbumChildrenChanged(['the-parent-id']));

		$this->assertEvicted('k:parent');
		// Must NOT imply is_pinned relevance — root/pinned untouched.
		$this->assertNotEvicted('k:root');
		$this->assertNotEvicted('k:pinned');
	}

	// ── TagAlbumSaved / PersonAlbumSaved ────────────────────────

	public function testTagAlbumSavedEvictsOwnTagAndTagAlbumsListingOnly(): void
	{
		$user = User::factory()->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($tag_album->id)]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);

		$this->listener->handleTagAlbumSaved(new TagAlbumSaved([$tag_album->id]));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:tag-albums');
		$this->assertNotEvicted('k:person-albums');
	}

	public function testPersonAlbumSavedEvictsOwnTagAndPersonAlbumsListingOnly(): void
	{
		$user = User::factory()->create();
		$person_album = new PersonAlbum();
		$person_album->title = 'Test';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($person_album->id)]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);

		$this->listener->handlePersonAlbumSaved(new PersonAlbumSaved($person_album));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:person-albums');
		$this->assertNotEvicted('k:tag-albums');
	}

	// ── BaseAlbumRemoved ────────────────────────────────────────

	public function testBaseAlbumRemovedEvictsBothTagAndPersonAlbumsListing(): void
	{
		$this->seedCache('k:album', [$this->cache_key_provider->albumTag('removed-id')]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);

		$this->listener->handleBaseAlbumRemoved(new BaseAlbumRemoved(['removed-id']));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:tag-albums');
		$this->assertEvicted('k:person-albums');
	}

	// ── AccessPermissionChanged ─────────────────────────────────

	public function testAccessPermissionChangedForRegularAlbumEvictsChildrenRootAndPinned(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$album = Album::factory()->children_of($parent)->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($album->id)]);
		$this->seedCache('k:parent', [$this->cache_key_provider->albumChildrenTag($parent->id)]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);

		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($album->id));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:parent');
		$this->assertEvicted('k:root');
		$this->assertEvicted('k:pinned');
		$this->assertNotEvicted('k:tag-albums');
	}

	/**
	 * Feature 061 (FR-061-22/T-061-34): the rights endpoint's cache is keyed
	 * by the queried album's *own* albumChildrenTag() (since
	 * can_delete_children/can_move_children are derived from grants on that
	 * album, not its parent) — a permission change directly against
	 * `$album->id` must evict `albumChildrenTag($album->id)` too, not just
	 * `albumChildrenTag($album->parent_id)`.
	 */
	public function testAccessPermissionChangedEvictsTheAlbumsOwnChildrenTag(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$album = Album::factory()->children_of($parent)->owned_by($user)->create();

		$this->seedCache('k:own-children', [$this->cache_key_provider->albumChildrenTag($album->id)]);

		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($album->id));

		$this->assertEvicted('k:own-children');
	}

	public function testAccessPermissionChangedForTagAlbumEvictsTagAlbumsListingOnly(): void
	{
		$user = User::factory()->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($tag_album->id)]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);

		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($tag_album->id));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:tag-albums');
		$this->assertNotEvicted('k:person-albums');
		$this->assertNotEvicted('k:root');
	}

	public function testAccessPermissionChangedForPersonAlbumEvictsPersonAlbumsListingOnly(): void
	{
		$user = User::factory()->create();
		$person_album = new PersonAlbum();
		$person_album->title = 'Test';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();

		$this->seedCache('k:album', [$this->cache_key_provider->albumTag($person_album->id)]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);
		$this->seedCache('k:person-albums', [$this->cache_key_provider->personAlbumsListingTag()]);

		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($person_album->id));

		$this->assertEvicted('k:album');
		$this->assertEvicted('k:person-albums');
		$this->assertNotEvicted('k:tag-albums');
	}

	// ── AlbumComputedDataUpdated ────────────────────────────────

	public function testAlbumComputedDataUpdatedEvictsOnlyOwnTag(): void
	{
		$this->seedCache('k:album', [$this->cache_key_provider->albumTag('some-id')]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);

		$this->listener->handleAlbumComputedDataUpdated(new AlbumComputedDataUpdated('some-id'));

		$this->assertEvicted('k:album');
		$this->assertNotEvicted('k:root');
		$this->assertNotEvicted('k:pinned');
	}

	// ── AlbumListingCacheFlushRequested ─────────────────────────

	public function testAlbumListingCacheFlushRequestedEvictsOnlyGlobalTag(): void
	{
		$this->seedCache('k:global', [$this->cache_key_provider->albumListingGlobalTag()]);
		$this->seedCache('k:root', [$this->cache_key_provider->albumChildrenTag(null)]);
		$this->seedCache('k:pinned', [$this->cache_key_provider->pinnedAlbumsListingTag()]);
		$this->seedCache('k:tag-albums', [$this->cache_key_provider->tagAlbumsListingTag()]);

		$this->listener->handleAlbumListingCacheFlushRequested(new AlbumListingCacheFlushRequested());

		$this->assertEvicted('k:global');
		$this->assertNotEvicted('k:root');
		$this->assertNotEvicted('k:pinned');
		$this->assertNotEvicted('k:tag-albums');
	}

	/**
	 * The root index/buckets/rights entries ({@see \App\Http\Controllers\Gallery\AlbumListing\AlbumRootController})
	 * carry albumListingGlobalTag() alongside their own albumChildrenTag(null)/userTag()
	 * pair, so the coarse flush event must evict all three even though it
	 * does not touch albumChildrenTag(null) itself.
	 */
	public function testAlbumListingCacheFlushRequestedEvictsAllThreeRootEntries(): void
	{
		$user = User::factory()->create();

		$index_key = $this->cache_key_provider->rootAlbumChildrenDataKey(AlbumListingScope::OWN, $user->id);
		$buckets_key = $this->cache_key_provider->rootAlbumBucketsKey(AlbumListingScope::OWN, $user->id);
		$rights_key = $this->cache_key_provider->rootAlbumChildrenRightsKey(AlbumListingScope::OWN, $user->id);

		$root_tags = [
			$this->cache_key_provider->albumChildrenTag(null),
			$this->cache_key_provider->userTag($user->id),
			$this->cache_key_provider->albumListingGlobalTag(),
		];
		$this->seedCache($index_key, $root_tags);
		$this->seedCache($buckets_key, $root_tags);
		$this->seedCache($rights_key, $root_tags);

		$this->listener->handleAlbumListingCacheFlushRequested(new AlbumListingCacheFlushRequested());

		$this->assertEvicted($index_key);
		$this->assertEvicted($buckets_key);
		$this->assertEvicted($rights_key);
	}

	/**
	 * NFR-053-06 spot check: none of the precise-eviction handlers should
	 * ever touch the coarse `album-listing-global` tag.
	 */
	public function testNonFlushEventsNeverTouchTheGlobalTag(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$this->seedCache('k:global', [$this->cache_key_provider->albumListingGlobalTag()]);

		$this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id]));
		$this->listener->handleAlbumDeleted(new AlbumDeleted(null));
		$this->listener->handleAlbumChildrenChanged(new AlbumChildrenChanged([null]));
		$this->listener->handleAlbumComputedDataUpdated(new AlbumComputedDataUpdated($album->id));
		$this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($album->id));

		$this->assertNotEvicted('k:global');
	}

	// ── AlbumTagsChanged ────────────────────────────────────────

	public function testAlbumTagsChangedEvictsEachTagId(): void
	{
		$this->seedCache('k:tag1', [$this->cache_key_provider->albumTagTag(1)]);
		$this->seedCache('k:tag2', [$this->cache_key_provider->albumTagTag(2)]);
		$this->seedCache('k:tag3', [$this->cache_key_provider->albumTagTag(3)]);

		$this->listener->handleAlbumTagsChanged(new AlbumTagsChanged([1, 2]));

		$this->assertEvicted('k:tag1');
		$this->assertEvicted('k:tag2');
		$this->assertNotEvicted('k:tag3');
	}

	// ── Feature 062 (FR-062-14, T-062-26a): the new root/persons/pinned ──
	// cache entries are tagged with the exact same tags this listener
	// already evicts — verify directly against the real key-generation
	// methods those endpoints use, not just the pre-existing generic keys
	// above.

	public function testAlbumSavedEvictsRootOwnAndSharedScopeBucketKeys(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$own_key = $this->cache_key_provider->rootAlbumBucketsKey(AlbumListingScope::OWN, $user->id);
		$shared_key = $this->cache_key_provider->rootAlbumChildrenDataKey(AlbumListingScope::SHARED, $user->id);
		$rights_key = $this->cache_key_provider->rootAlbumChildrenRightsKey(AlbumListingScope::OWN, null);

		$this->seedCache($own_key, [$this->cache_key_provider->albumChildrenTag(null), $this->cache_key_provider->userTag($user->id)]);
		$this->seedCache($shared_key, [$this->cache_key_provider->albumChildrenTag(null), $this->cache_key_provider->userTag($user->id)]);
		$this->seedCache($rights_key, [$this->cache_key_provider->albumChildrenTag(null), $this->cache_key_provider->userTag(null)]);

		$this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id]));

		$this->assertEvicted($own_key);
		$this->assertEvicted($shared_key);
		$this->assertEvicted($rights_key);
	}

	public function testAlbumListingCacheFlushRequestedDoesNotEvictRootScopeKeysDirectlyButGlobalTagAloneIsSufficient(): void
	{
		// Root cache entries are tagged with albumChildrenTag(null), not the
		// coarse global tag directly — but albumChildrenTag(null) is itself
		// already evicted by handleAlbumSaved/handleAlbumDeleted/
		// handleAccessPermissionChanged above; this case documents that the
		// coarse flush event does NOT separately need to know about root's
		// new keys, since it's a distinct, deliberately narrower event.
		$key = $this->cache_key_provider->rootAlbumBucketsKey(AlbumListingScope::SHARED, null);
		$this->seedCache($key, [$this->cache_key_provider->albumChildrenTag(null), $this->cache_key_provider->userTag(null)]);

		$this->listener->handleAlbumListingCacheFlushRequested(new AlbumListingCacheFlushRequested());

		$this->assertNotEvicted($key);
	}

	public function testTagAlbumSavedEvictsTagsCategoryKeyOnly(): void
	{
		$user = User::factory()->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		$tags_key = 'tag-albums-listing:' . $this->cache_key_provider->userTag($user->id) . ':sort:created_at:ASC';
		$this->seedCache($tags_key, [$this->cache_key_provider->tagAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);

		$this->listener->handleTagAlbumSaved(new TagAlbumSaved([$tag_album->id]));

		$this->assertEvicted($tags_key);
	}

	public function testPersonAlbumSavedEvictsPersonsOwnAndSharedScopeKeysOnly(): void
	{
		$user = User::factory()->create();
		$person_album = new PersonAlbum();
		$person_album->title = 'Test';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();

		$own_key = $this->cache_key_provider->personAlbumsListingKey($user->id, AlbumSortingCriterion::createDefault(), AlbumListingScope::OWN);
		$shared_key = $this->cache_key_provider->personAlbumsListingKey($user->id, AlbumSortingCriterion::createDefault(), AlbumListingScope::SHARED);
		$pinned_key = $this->cache_key_provider->pinnedAlbumsListingKey($user->id, null, null, AlbumListingScope::OWN);

		$this->seedCache($own_key, [$this->cache_key_provider->personAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);
		$this->seedCache($shared_key, [$this->cache_key_provider->personAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);
		$this->seedCache($pinned_key, [$this->cache_key_provider->pinnedAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);

		$this->listener->handlePersonAlbumSaved(new PersonAlbumSaved($person_album));

		$this->assertEvicted($own_key);
		$this->assertEvicted($shared_key);
		// pinned must stay independent of a PersonAlbumSaved event.
		$this->assertNotEvicted($pinned_key);
	}

	public function testAlbumSavedEvictsPinnedOwnAndSharedScopeKeys(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$own_key = $this->cache_key_provider->pinnedAlbumsListingKey($user->id, null, null, AlbumListingScope::OWN);
		$shared_key = $this->cache_key_provider->pinnedAlbumsListingKey($user->id, null, null, AlbumListingScope::SHARED);

		$this->seedCache($own_key, [$this->cache_key_provider->pinnedAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);
		$this->seedCache($shared_key, [$this->cache_key_provider->pinnedAlbumsListingTag(), $this->cache_key_provider->userTag($user->id)]);

		$this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id]));

		$this->assertEvicted($own_key);
		$this->assertEvicted($shared_key);
	}

	// ── Feature 057: album-listing-v3 tag (FR-057-06) ────────────
	//
	// Every handler in this listener must also evict the v3 listing cache,
	// regardless of how narrowly it otherwise scopes its own eviction.

	public function testEveryHandlerEvictsTheAlbumListingV3Tag(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$album = Album::factory()->children_of($parent)->owned_by($user)->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();
		$person_album = new PersonAlbum();
		$person_album->title = 'Test';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();

		$v3_tag = $this->cache_key_provider->albumListingV3Tag();

		$cases = [
			'AlbumSaved' => fn () => $this->listener->handleAlbumSaved(new AlbumSaved([$album->id], [$album->parent_id])),
			'AlbumDeleted' => fn () => $this->listener->handleAlbumDeleted(new AlbumDeleted($parent->id)),
			'AlbumChildrenChanged' => fn () => $this->listener->handleAlbumChildrenChanged(new AlbumChildrenChanged([$parent->id])),
			'TagAlbumSaved' => fn () => $this->listener->handleTagAlbumSaved(new TagAlbumSaved([$tag_album->id])),
			'PersonAlbumSaved' => fn () => $this->listener->handlePersonAlbumSaved(new PersonAlbumSaved($person_album)),
			'BaseAlbumRemoved' => fn () => $this->listener->handleBaseAlbumRemoved(new BaseAlbumRemoved([$album->id])),
			'AccessPermissionChanged' => fn () => $this->listener->handleAccessPermissionChanged(new AccessPermissionChanged($album->id)),
			'AlbumComputedDataUpdated' => fn () => $this->listener->handleAlbumComputedDataUpdated(new AlbumComputedDataUpdated($album->id)),
			'AlbumListingCacheFlushRequested' => fn () => $this->listener->handleAlbumListingCacheFlushRequested(new AlbumListingCacheFlushRequested()),
			'AlbumTagsChanged' => fn () => $this->listener->handleAlbumTagsChanged(new AlbumTagsChanged([1])),
			'PhotoPersonsChanged' => fn () => $this->listener->handlePhotoPersonsChanged(new PhotoPersonsChanged(['some-photo-id'], [])),
			'PhotoMoved' => fn () => $this->listener->handlePhotoMoved(new PhotoMoved(['some-photo-id'], 'from-album-id', 'to-album-id')),
			'PhotoSaved' => fn () => $this->listener->handlePhotoSaved(new PhotoSaved(['some-photo-id'])),
			'PhotoWillBeDeleted' => fn () => $this->listener->handlePhotoWillBeDeleted(new PhotoWillBeDeleted('some-photo-id', $album->id, 'title', [])),
		];

		foreach ($cases as $event_name => $trigger) {
			$this->seedCache('k:v3', [$v3_tag]);
			$trigger();
			$this->assertEvicted('k:v3');
			// Re-seed for the next iteration; assertEvicted already confirmed eviction above.
		}
	}
}
