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

namespace Tests\Unit\Actions\Photo\Pipes\Shared;

use App\Actions\Photo\Pipes\Shared\GeodecodeLocation;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Jobs\GeodecodeLocationJob;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\AbstractTestCase;

class GeodecodeLocationTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testSkipsWhenLocationDecodingIsDisabled(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('location_decoding')
			->andReturn(false);

		$pipe = new GeodecodeLocation($config_manager);

		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
		]);
		$state = $this->createMockPhotoDTO($photo);

		$nextCalled = false;
		$next = function (PhotoDTO $state) use (&$nextCalled): PhotoDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
		Queue::assertNothingPushed();
	}

	public function testSkipsWhenLatitudeIsNull(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('location_decoding')
			->andReturn(true);

		$pipe = new GeodecodeLocation($config_manager);

		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => null,
			'longitude' => 2.3522,
		]);
		$state = $this->createMockPhotoDTO($photo);

		$nextCalled = false;
		$next = function (PhotoDTO $state) use (&$nextCalled): PhotoDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
		Queue::assertNothingPushed();
	}

	public function testSkipsWhenLongitudeIsNull(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('location_decoding')
			->andReturn(true);

		$pipe = new GeodecodeLocation($config_manager);

		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => null,
		]);
		$state = $this->createMockPhotoDTO($photo);

		$nextCalled = false;
		$next = function (PhotoDTO $state) use (&$nextCalled): PhotoDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
		Queue::assertNothingPushed();
	}

	public function testDispatchesJobWhenConditionsAreMet(): void
	{
		Queue::fake();

		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')
			->once()
			->with('location_decoding')
			->andReturn(true);

		$pipe = new GeodecodeLocation($config_manager);

		$user = User::factory()->create();
		$photo = Photo::factory()->create([
			'owner_id' => $user->id,
			'latitude' => 48.8566,
			'longitude' => 2.3522,
		]);
		$state = $this->createMockPhotoDTO($photo);

		$nextCalled = false;
		$next = function (PhotoDTO $state) use (&$nextCalled): PhotoDTO {
			$nextCalled = true;

			return $state;
		};

		$result = $pipe->handle($state, $next);

		$this->assertTrue($nextCalled);
		$this->assertSame($state, $result);
		Queue::assertPushed(GeodecodeLocationJob::class, function ($job) use ($photo): bool {
			return $job->photo_id === $photo->id;
		});
	}

	/**
	 * Create a mock PhotoDTO with the given Photo.
	 *
	 * @param Photo $photo
	 *
	 * @return PhotoDTO
	 */
	private function createMockPhotoDTO(Photo $photo): PhotoDTO
	{
		$dto = \Mockery::mock(PhotoDTO::class);
		$dto->shouldReceive('getPhoto')
			->andReturn($photo);

		return $dto;
	}
}
