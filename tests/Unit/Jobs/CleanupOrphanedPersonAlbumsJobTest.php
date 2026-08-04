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

use App\Actions\Album\Delete;
use App\Jobs\CleanupOrphanedPersonAlbumsJob;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class CleanupOrphanedPersonAlbumsJobTest extends AbstractTestCase
{
	public function testDoesNothingWhenNoOrphansFound(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('leftJoin')->once()->andReturnSelf();
		$builder->shouldReceive('whereNull')->once()->with('person_albums_persons.id')->andReturnSelf();
		$builder->shouldReceive('pluck')->once()->with('person_albums.id')->andReturnSelf();
		$builder->shouldReceive('all')->once()->andReturn([]);
		DB::shouldReceive('table')->once()->with('person_albums')->andReturn($builder);

		$this->mock(Delete::class, function (MockInterface $mock): void {
			$mock->shouldNotReceive('do');
		});

		(new CleanupOrphanedPersonAlbumsJob())->handle();
		self::assertTrue(true);
	}

	public function testDeletesOrphanedPersonAlbums(): void
	{
		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('leftJoin')->once()->andReturnSelf();
		$builder->shouldReceive('whereNull')->once()->with('person_albums_persons.id')->andReturnSelf();
		$builder->shouldReceive('pluck')->once()->with('person_albums.id')->andReturnSelf();
		$builder->shouldReceive('all')->once()->andReturn(['orphan-1', 'orphan-2']);
		DB::shouldReceive('table')->once()->with('person_albums')->andReturn($builder);

		$this->mock(Delete::class, function (MockInterface $mock): void {
			$mock->shouldReceive('do')->once()->with(['orphan-1', 'orphan-2']);
		});

		(new CleanupOrphanedPersonAlbumsJob())->handle();
		self::assertTrue(true);
	}
}
