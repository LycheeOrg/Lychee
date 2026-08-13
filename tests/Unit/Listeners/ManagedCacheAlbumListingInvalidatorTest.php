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

use App\Events\AccessPermissionChanged;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumComputedDataUpdated;
use App\Events\AlbumDeleted;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\AlbumTagsChanged;
use App\Events\BaseAlbumRemoved;
use App\Events\PersonAlbumSaved;
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
}
