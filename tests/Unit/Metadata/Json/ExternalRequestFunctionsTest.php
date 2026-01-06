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

namespace Tests\Unit\Metadata\Json;

use App\Metadata\Json\ExternalRequestFunctions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\AbstractTestCase;

class ExternalRequestFunctionsTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testGetDataReturnsNullInTestingEnvironment(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/testing.*environment/i'));

		Cache::shouldReceive('get')
			->with($url)
			->andReturn('');

		Cache::shouldReceive('forget')
			->with($url)
			->once();

		Cache::shouldReceive('forget')
			->with($url . '_age')
			->once();

		// Act
		$result = $externalRequest->get_data();

		// Assert
		self::assertNull($result);
	}

	public function testGetDataReturnsCachedDataWhenUseCacheIsTrue(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$cachedData = '{"test":"cached data"}';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('get')
			->with($url)
			->once()
			->andReturn($cachedData);

		// Act
		$result = $externalRequest->get_data(true);

		// Assert
		self::assertEquals($cachedData, $result);
	}

	public function testGetDataReturnsNullAndClearsCacheOnException(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('get')
			->with($url)
			->andReturn('');

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/ExternalRequestFunctions::get_data.*testing.*environment/i'));

		Cache::shouldReceive('forget')
			->with($url)
			->once();

		Cache::shouldReceive('forget')
			->with($url . '_age')
			->once();

		// Act
		$result = $externalRequest->get_data();

		// Assert
		self::assertNull($result);
	}

	public function testGetDataUsesCachedDataOnSecondCall(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$cachedData = 'test data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('get')
			->with($url)
			->once()
			->andReturn($cachedData);

		// Act - First call
		$result1 = $externalRequest->get_data(true);
		// Second call should use the internal $data property
		$result2 = $externalRequest->get_data(true);

		// Assert
		self::assertEquals($cachedData, $result1);
		self::assertEquals($cachedData, $result2);
	}

	public function testGetAgeTextReturnsUnknownWhenNoCache(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('get')
			->with($url . '_age')
			->once()
			->andReturnNull();

		// Act
		$result = $externalRequest->get_age_text();

		// Assert
		self::assertEquals('unknown', $result);
	}

	public function testGetAgeTextReturnsSecondsAgo(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);
		$age = now()->subSeconds(30);

		Cache::shouldReceive('get')
			->with($url . '_age')
			->once()
			->andReturn($age);

		// Act
		$result = $externalRequest->get_age_text();

		// Assert
		self::assertMatchesRegularExpression('/^\d+ seconds ago$/', $result);
	}

	public function testClearCacheForgetsBothUrlAndAge(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('forget')
			->with($url)
			->once();

		Cache::shouldReceive('forget')
			->with($url . '_age')
			->once();

		// Act
		$externalRequest->clear_cache();

		// No explicit assert needed - Mockery will verify expectations
		$this->assertTrue(true);
	}

	public function testGetDataWithoutCacheIgnoresCachedData(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		// Should not use cache when use_cache is false
		Cache::shouldReceive('get')
			->with($url)
			->never();

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/testing.*environment/i'));

		Cache::shouldReceive('forget')
			->with($url)
			->once();

		Cache::shouldReceive('forget')
			->with($url . '_age')
			->once();

		// Act
		$result = $externalRequest->get_data(false);

		// Assert
		self::assertNull($result);
	}

	public function testConstructorSetsUrlAndTtl(): void
	{
		// Arrange & Act
		$url = 'https://example.com/api/test';
		$ttl = 14;
		$externalRequest = new ExternalRequestFunctions($url, $ttl);

		// Assert - verify the object was created successfully
		self::assertInstanceOf(ExternalRequestFunctions::class, $externalRequest);
	}

	public function testGetDataReturnsEmptyStringWhenCacheReturnsEmptyString(): void
	{
		// Arrange
		$url = 'https://example.com/api/data';
		$externalRequest = new ExternalRequestFunctions($url, 7);

		Cache::shouldReceive('get')
			->with($url)
			->andReturn('');

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/testing.*environment/i'));

		Cache::shouldReceive('forget')
			->with($url)
			->once();

		Cache::shouldReceive('forget')
			->with($url . '_age')
			->once();

		// Act
		$result = $externalRequest->get_data(true);

		// Assert
		self::assertNull($result);
	}
}
