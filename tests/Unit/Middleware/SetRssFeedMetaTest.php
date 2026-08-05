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

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetRssFeedMeta;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

class SetRssFeedMetaTest extends AbstractTestCase
{
	private function requestWithConfigs(\Mockery\MockInterface $config_manager): Request
	{
		$request = Request::create('/feed');
		$request->attributes->set('configs', $config_manager);

		return $request;
	}

	public function testOverridesTitleAndDescriptionWhenSet(): void
	{
		Config::set('feed.feeds.main.title', 'Default title');
		Config::set('feed.feeds.main.description', 'Default description');

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('hasKey')->once()->with('rss_title')->andReturn(true);
		$config_manager->shouldReceive('getValueAsString')->once()->with('rss_title')->andReturn('My Gallery');
		$config_manager->shouldReceive('hasKey')->once()->with('rss_description')->andReturn(true);
		$config_manager->shouldReceive('getValueAsString')->once()->with('rss_description')->andReturn('My Description');

		$request = $this->requestWithConfigs($config_manager);
		$middleware = new SetRssFeedMeta();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertEquals('My Gallery', config('feed.feeds.main.title'));
		self::assertEquals('My Description', config('feed.feeds.main.description'));
	}

	public function testKeepsDefaultsWhenValuesAreBlank(): void
	{
		Config::set('feed.feeds.main.title', 'Default title');
		Config::set('feed.feeds.main.description', 'Default description');

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('hasKey')->once()->with('rss_title')->andReturn(true);
		$config_manager->shouldReceive('getValueAsString')->once()->with('rss_title')->andReturn('');
		$config_manager->shouldReceive('hasKey')->once()->with('rss_description')->andReturn(true);
		$config_manager->shouldReceive('getValueAsString')->once()->with('rss_description')->andReturn('');

		$request = $this->requestWithConfigs($config_manager);
		$middleware = new SetRssFeedMeta();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertEquals('Default title', config('feed.feeds.main.title'));
		self::assertEquals('Default description', config('feed.feeds.main.description'));
	}

	public function testKeepsDefaultsWhenKeysAreMissing(): void
	{
		Config::set('feed.feeds.main.title', 'Default title');
		Config::set('feed.feeds.main.description', 'Default description');

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('hasKey')->once()->with('rss_title')->andReturn(false);
		$config_manager->shouldReceive('hasKey')->once()->with('rss_description')->andReturn(false);

		$request = $this->requestWithConfigs($config_manager);
		$middleware = new SetRssFeedMeta();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
		self::assertEquals('Default title', config('feed.feeds.main.title'));
		self::assertEquals('Default description', config('feed.feeds.main.description'));
	}
}
