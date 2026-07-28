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

namespace Tests\Unit\Console\Profiling;

use App\Constants\FileSystem;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

class PruneTracesTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}
	}

	protected function tearDown(): void
	{
		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}
		parent::tearDown();
	}

	public function testCommandPrunesBeyondCap(): void
	{
		config(['features.memory-profiler-max-traces' => 1]);
		$disk = Storage::disk(FileSystem::PROFILING);

		foreach (['a', 'b', 'c'] as $i => $basename) {
			$disk->put($basename . '.pprof', 'content');
			$disk->put($basename . '.json', json_encode([
				'route_name' => null,
				'method' => 'GET',
				'path' => 'foo',
				'status_code' => 200,
				'duration_ms' => 1.0,
				'peak_memory_bytes' => 1,
				'user_id' => null,
				'created_at' => sprintf('2026-07-28T%02d:00:00+00:00', $i),
			]));
		}

		$this->artisan('lychee:profiler:prune')
			->expectsOutputToContain('Removed 2 trace pair(s)')
			->assertExitCode(0);

		self::assertCount(2, Storage::disk(FileSystem::PROFILING)->allFiles());
	}
}
