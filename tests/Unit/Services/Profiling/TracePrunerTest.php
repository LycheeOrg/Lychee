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

namespace Tests\Unit\Services\Profiling;

use App\Constants\FileSystem;
use App\Services\Profiling\TracePruner;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

class TracePrunerTest extends AbstractTestCase
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

	private function seedTrace(string $sidecar_basename, string $spx_report_key, string $created_at): void
	{
		$disk = Storage::disk(FileSystem::PROFILING);
		$disk->put('lychee-' . $sidecar_basename . '.json', json_encode([
			'spx_report_key' => $spx_report_key,
			'route_name' => null,
			'method' => 'GET',
			'path' => 'foo',
			'status_code' => 200,
			'duration_ms' => 1.0,
			'peak_memory_bytes' => 1,
			'user_id' => null,
			'created_at' => $created_at,
		]));
		$disk->put($spx_report_key . '.json', '{"key":"' . $spx_report_key . '"}');
		$disk->put($spx_report_key . '.txt.gz', 'content');
	}

	public function testKeepsAllWhenUnderCap(): void
	{
		config(['features.memory-profiler-max-traces' => 5]);

		$this->seedTrace('a', 'spx-full-a', '2026-07-28T10:00:00+00:00');
		$this->seedTrace('b', 'spx-full-b', '2026-07-28T10:01:00+00:00');

		$removed = (new TracePruner())->prune();

		self::assertSame(0, $removed);
		self::assertCount(6, Storage::disk(FileSystem::PROFILING)->allFiles());
	}

	public function testPrunesOldestBeyondCap(): void
	{
		config(['features.memory-profiler-max-traces' => 2]);

		$this->seedTrace('oldest', 'spx-full-oldest', '2026-07-28T09:00:00+00:00');
		$this->seedTrace('middle', 'spx-full-middle', '2026-07-28T10:00:00+00:00');
		$this->seedTrace('newest', 'spx-full-newest', '2026-07-28T11:00:00+00:00');

		$removed = (new TracePruner())->prune();

		self::assertSame(1, $removed);

		$disk = Storage::disk(FileSystem::PROFILING);
		self::assertFalse($disk->exists('lychee-oldest.json'));
		self::assertFalse($disk->exists('spx-full-oldest.json'));
		self::assertFalse($disk->exists('spx-full-oldest.txt.gz'));
		self::assertTrue($disk->exists('lychee-middle.json'));
		self::assertTrue($disk->exists('spx-full-middle.json'));
		self::assertTrue($disk->exists('lychee-newest.json'));
		self::assertTrue($disk->exists('spx-full-newest.json'));
	}
}
