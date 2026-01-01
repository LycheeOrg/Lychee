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

use App\Metadata\Json\JsonRequestFunctions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class JsonRequestFunctionsTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testInstanciate(): void
	{
		$update_request = new class('url', 5) extends JsonRequestFunctions {
			public function setData($data)
			{
				$this->data = $data;
			}
		};

		$update_request->setData('{}');
		$ret = $update_request->get_json();
		self::assertNull($ret);
	}

	public function testGetJsonReturnsNullWhenDataIsNull(): void
	{
		// Arrange
		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn('');

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/ExternalRequestFunctions::get_data.*testing.*environment/i'));

		Cache::shouldReceive('forget')
			->with('https://example.com/test')
			->once();

		Cache::shouldReceive('forget')
			->with('https://example.com/test_age')
			->once();

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json();

		// Assert
		self::assertNull($result);
	}

	public function testGetJsonReturnsDecodedObjectWhenDataIsValidJson(): void
	{
		// Arrange
		$jsonData = '{"name":"test","value":123}';

		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn($jsonData);

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json(true);

		// Assert
		self::assertIsObject($result);
		self::assertEquals('test', $result->name);
		self::assertEquals(123, $result->value);
	}

	public function testGetJsonReturnsDecodedArrayWhenDataIsValidJsonArray(): void
	{
		// Arrange
		$jsonData = '[1,2,3,4,5]';

		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn($jsonData);

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json(true);

		// Assert
		self::assertIsArray($result);
		self::assertCount(5, $result);
		self::assertEquals([1, 2, 3, 4, 5], $result);
	}

	public function testGetJsonReturnsNullAndLogsErrorWhenDataIsInvalidJson(): void
	{
		// Arrange
		$invalidJsonData = '{invalid json';

		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn($invalidJsonData);

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/JsonRequestFunctions::get_json.*Syntax error/i'));

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json(true);

		// Assert
		self::assertNull($result);
	}

	public function testGetJsonReturnsDecodedPrimitiveWhenDataIsValidJsonPrimitive(): void
	{
		// Arrange
		$jsonData = '42';

		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn($jsonData);

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json(true);

		// Assert
		self::assertEquals(42, $result);
	}

	public function testGetJsonReturnsDecodedStringWhenDataIsValidJsonString(): void
	{
		// Arrange
		$jsonData = '"test string"';

		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn($jsonData);

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json(true);

		// Assert
		self::assertEquals('test string', $result);
	}

	public function testGetJsonReturnsNullWhenDataIsEmptyString(): void
	{
		// Arrange
		Cache::shouldReceive('get')
			->with('https://example.com/test')
			->andReturn('');

		Log::shouldReceive('error')
			->once()
			->with(\Mockery::pattern('/ExternalRequestFunctions::get_data.*testing.*environment/i'));

		Cache::shouldReceive('forget')
			->with('https://example.com/test')
			->once();

		Cache::shouldReceive('forget')
			->with('https://example.com/test_age')
			->once();

		$jsonRequest = new class('https://example.com/test', 1) extends JsonRequestFunctions {
		};

		// Act
		$result = $jsonRequest->get_json();

		// Assert
		self::assertNull($result);
	}
}
