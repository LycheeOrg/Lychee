<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
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
use App\Events\PhotoDeleted;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoMoved;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
use App\Events\UserGroupMembershipChanged;
use App\Listeners\ManagedCacheSearchListingInvalidator;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

/**
 * Covers {@see ManagedCacheSearchListingInvalidator} (T-069-48/49, FR-069-23,
 * FR-069-24).
 *
 * The listener body is a single unconditional `forgetTag()` call, so the part
 * that can actually regress is not the handler but the *registration*: which
 * events reach it at all. That is what {@see self::testResultAffectingEventIsRegistered()}
 * asserts, against the dispatcher's own raw listener map.
 *
 * Direct structural precedent:
 * {@see \Tests\Unit\Listeners\ManagedCacheMapListingInvalidatorTest}.
 */
class ManagedCacheSearchListingInvalidatorTest extends AbstractTestCase
{
	private ManagedCacheService $cache_service;
	private CacheKeyProvider $cache_key_provider;
	private ManagedCacheSearchListingInvalidator $listener;

	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->with('managed_cache_enabled')->andReturn(true);

		$this->cache_service = new ManagedCacheService($config_manager);
		$this->cache_key_provider = new CacheKeyProvider();
		$this->listener = new ManagedCacheSearchListingInvalidator($this->cache_service, $this->cache_key_provider);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * Every event that must evict the v3 search cache.
	 *
	 * @return array<string,array{0:class-string}>
	 */
	public static function resultAffectingEventProvider(): array
	{
		return [
			// Photo-side mutations (already wired by T-069-30).
			'photo saved' => [PhotoSaved::class],
			'photo moved' => [PhotoMoved::class],
			'photo deleted' => [PhotoDeleted::class],
			'photo tags changed' => [PhotoTagsChanged::class],
			'photo rating changed' => [PhotoRatingChanged::class],
			'photo highlight toggled' => [PhotoHighlightToggled::class],
			// Album data: title/description (FR-069-07's own match columns),
			// album tags, parentage (the `_lft`/`_rgt` origin bounds of
			// FR-069-12), computed data, and deletion.
			'album saved' => [AlbumSaved::class],
			'album deleted' => [AlbumDeleted::class],
			'album tags changed' => [AlbumTagsChanged::class],
			'album children changed' => [AlbumChildrenChanged::class],
			'album computed data updated' => [AlbumComputedDataUpdated::class],
			'album listing cache flush requested' => [AlbumListingCacheFlushRequested::class],
			// Access permissions: security-critical, not merely freshness
			// (FR-069-24) - a cache hit never re-runs the browsability filter.
			'access permission changed' => [AccessPermissionChanged::class],
			'user group membership changed' => [UserGroupMembershipChanged::class],
		];
	}

	/**
	 * @param class-string $event_class
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('resultAffectingEventProvider')]
	public function testResultAffectingEventIsRegistered(string $event_class): void
	{
		/** @var Dispatcher $dispatcher */
		$dispatcher = Event::getFacadeRoot();
		$raw_listeners = $dispatcher->getRawListeners();

		self::assertArrayHasKey($event_class, $raw_listeners, "no listener at all is registered for [$event_class]");
		self::assertContains(
			ManagedCacheSearchListingInvalidator::class . '@handle',
			$raw_listeners[$event_class],
			"[$event_class] does not evict the v3 search cache"
		);
	}

	public function testHandleEvictsEverySearchEntryRegardlessOfTier(): void
	{
		$tag = $this->cache_key_provider->searchListingTag();
		$this->cache_service->remember('k:photos', [$tag], fn () => 'value', ttl: 60);
		$this->cache_service->remember('k:albums', [$tag], fn () => 'value', ttl: 60);
		$this->cache_service->remember('k:untagged', ['some-other-tag'], fn () => 'value', ttl: 60);

		$this->listener->handle(new AccessPermissionChanged('some-album-id'));

		self::assertNull(Cache::get('k:photos'), 'expected the photo tier to have been evicted');
		self::assertNull(Cache::get('k:albums'), 'expected the album tier to have been evicted');
		self::assertNotNull(Cache::get('k:untagged'), 'expected an unrelated cache entry to survive');
	}
}
