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

namespace Tests\Unit\Jobs;

use App\Enum\JobStatus;
use App\Jobs\GeodecodeLocationJob;
use App\Metadata\Extractor;
use App\Metadata\Geodecoder;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Unit tests for GeodecodeLocationJob.
 */
class GeodecodeLocationJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testConstructorCreatesJobHistory(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'title' => 'Test Photo',
			'latitude' => 48.8566,
			'longitude' => 2.3522,
		]);

		$job = new GeodecodeLocationJob($photo);

		$this->assertNotNull($job->photo_id);
		$this->assertEquals($photo->id, $job->photo_id);

		// Verify JobHistory was created
		$history = JobHistory::query()
			->where('owner_id', '=', $user->id)
			->where('status', '=', JobStatus::READY)
			->latest()
			->first();

		$this->assertNotNull($history);
		$this->assertStringContainsString('Geodecode location for Test Photo', $history->job);
		$this->assertStringContainsString($photo->id, $history->job);
	}

	public function testHandleSkipsWhenLatitudeIsNull(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => null,
			'longitude' => 2.3522,
			'location' => null,
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldNotReceive('getGeocoderProvider')
			->shouldNotReceive('decodeLocation_core');

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo was not updated
		$photo->refresh();
		$this->assertNull($photo->location);

		// Verify job history status is SUCCESS
		$history = JobHistory::query()
			->where('owner_id', '=', $user->id)
			->latest()
			->first();

		$this->assertNotNull($history);
		$this->assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testHandleSkipsWhenLongitudeIsNull(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => null,
			'location' => null,
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldNotReceive('getGeocoderProvider')
			->shouldNotReceive('decodeLocation_core');

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo was not updated
		$photo->refresh();
		$this->assertNull($photo->location);

		// Verify job history status is SUCCESS
		$history = JobHistory::query()
			->where('owner_id', '=', $user->id)
			->latest()
			->first();

		$this->assertNotNull($history);
		$this->assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testHandleDecodesLocationSuccessfully(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
			'location' => null,
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldReceive('getGeocoderProvider')
			->once()
			->andReturn(\Mockery::mock())
			->shouldReceive('decodeLocation_core')
			->once()
			->with(48.8566, 2.3522, \Mockery::any())
			->andReturn('Paris, France');

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo was updated
		$photo->refresh();
		$this->assertEquals('Paris, France', $photo->location);

		// Verify job history status is SUCCESS
		$history = JobHistory::query()
			->where('owner_id', '=', $user->id)
			->latest()
			->first();

		$this->assertNotNull($history);
		$this->assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testHandleDoesNotUpdatePhotoWithExistingLocation(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
			'location' => 'Existing Location',
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldReceive('getGeocoderProvider')
			->once()
			->andReturn(\Mockery::mock())
			->shouldReceive('decodeLocation_core')
			->once()
			->with(48.8566, 2.3522, \Mockery::any())
			->andReturn('Paris, France');

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo was NOT updated (location remains the same)
		$photo->refresh();
		$this->assertEquals('Existing Location', $photo->location);
	}

	public function testHandleUpdatesPhotoWithEmptyLocation(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
			'location' => '',
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldReceive('getGeocoderProvider')
			->once()
			->andReturn(\Mockery::mock())
			->shouldReceive('decodeLocation_core')
			->once()
			->with(48.8566, 2.3522, \Mockery::any())
			->andReturn('Paris, France');

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo was updated
		$photo->refresh();
		$this->assertEquals('Paris, France', $photo->location);
	}

	public function testHandleTruncatesLongLocation(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
			'location' => null,
		]);

		// Create a location string longer than MAX_LOCATION_STRING_LENGTH
		$longLocation = str_repeat('A', Extractor::MAX_LOCATION_STRING_LENGTH + 50);
		$expectedLocation = str_repeat('A', Extractor::MAX_LOCATION_STRING_LENGTH);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldReceive('getGeocoderProvider')
			->once()
			->andReturn(\Mockery::mock())
			->shouldReceive('decodeLocation_core')
			->once()
			->with(48.8566, 2.3522, \Mockery::any())
			->andReturn($longLocation);

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo location was truncated
		$photo->refresh();
		$this->assertEquals($expectedLocation, $photo->location);
		$this->assertEquals(Extractor::MAX_LOCATION_STRING_LENGTH, strlen($photo->location));
	}

	public function testHandleStoresNullWhenGeodecoderReturnsNull(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
			'location' => null,
		]);

		\Mockery::mock('alias:' . Geodecoder::class)
			->shouldReceive('getGeocoderProvider')
			->once()
			->andReturn(\Mockery::mock())
			->shouldReceive('decodeLocation_core')
			->once()
			->with(48.8566, 2.3522, \Mockery::any())
			->andReturn(null);

		$job = new GeodecodeLocationJob($photo);
		$job->handle();

		// Verify photo location is null
		$photo->refresh();
		$this->assertNull($photo->location);

		// Verify job history status is SUCCESS
		$history = JobHistory::query()
			->where('owner_id', '=', $user->id)
			->latest()
			->first();

		$this->assertNotNull($history);
		$this->assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testMiddlewareIncludesRateLimiter(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
		]);

		$job = new GeodecodeLocationJob($photo);
		$middleware = $job->middleware();

		$this->assertIsArray($middleware);
		$this->assertCount(1, $middleware);
		$this->assertInstanceOf(\Illuminate\Queue\Middleware\RateLimited::class, $middleware[0]);
	}
}
