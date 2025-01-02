<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Metadata\Cache;

use App\Enum\CacheTag;
use App\Metadata\Cache\RouteCacheManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class RouteCacheManagerTest extends AbstractTestCase
{
	use DatabaseTransactions;
	private RouteCacheManager $route_cache_manager;

	public function setUp(): void
	{
		parent::setUp();
		$this->route_cache_manager = new RouteCacheManager();
	}

	public function testNoConfig(): void
	{
		Log::shouldReceive('warning')->once();
		self::assertFalse($this->route_cache_manager->get_config('fake_url'));
	}

	public function testConfigFalse(): void
	{
		self::assertFalse($this->route_cache_manager->get_config('api/v2/Version'));
	}

	public function testConfigValid(): void
	{
		self::assertIsObject($this->route_cache_manager->get_config('api/v2/Album'));
	}

	public function testGenKey(): void
	{
		self::assertEquals('R:api/v2/Albums|U:', $this->route_cache_manager->gen_key('api/v2/Albums'));
		self::assertEquals('R:api/v2/Albums|U:1', $this->route_cache_manager->gen_key('api/v2/Albums', 1));
		self::assertEquals('R:api/v2/Album|U:1|E::2', $this->route_cache_manager->gen_key('api/v2/Album', 1, ['album_id' => '2']));
	}

	public function testGetFromTag(): void
	{
		$routes = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY);
		self::assertIsArray($routes);
		self::assertContains('api/v2/Album', $routes);
	}

	public function testGetFromTagWithExtra(): void
	{
		$routes = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, with_extra: true);
		self::assertIsArray($routes);
		self::assertContains('api/v2/Album', $routes);
		self::assertNotContains('api/v2/Albums', $routes);
	}

	public function testGetFromTagWithOutExtra(): void
	{
		$routes = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, without_extra: true);
		self::assertIsArray($routes);
		self::assertContains('api/v2/Albums', $routes);
		self::assertNotContains('api/v2/Album', $routes);
	}
}

