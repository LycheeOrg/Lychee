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

use App\Constants\FileSystem;
use App\Http\Middleware\MemoryProfiler;
use App\Services\Profiling\MemprofRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

/**
 * Fake recorder so these tests never require the real `memprof` PECL
 * extension to be installed (see spec.md NFR-053-05 / plan.md risk notes).
 */
class FakeMemprofRecorder extends MemprofRecorder
{
	public int $enable_calls = 0;
	public int $disable_calls = 0;
	public int $dump_calls = 0;
	public bool $available = true;
	public bool $throw_on_dump = false;

	public function isAvailable(): bool
	{
		return $this->available;
	}

	public function enable(): void
	{
		$this->enable_calls++;
	}

	public function disable(): void
	{
		$this->disable_calls++;
	}

	public function dumpPprof(string $absolute_path): void
	{
		$this->dump_calls++;
		if ($this->throw_on_dump) {
			throw new \RuntimeException('simulated dump failure');
		}
		file_put_contents($absolute_path, 'fake-pprof-content');
	}
}

class MemoryProfilerTest extends AbstractTestCase
{
	private FakeMemprofRecorder $recorder;

	protected function setUp(): void
	{
		parent::setUp();
		$this->recorder = new FakeMemprofRecorder();

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

	public function testNoOpWhenFeatureFlagOff(): void
	{
		config(['features.memory-profiler' => false]);
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo', 'GET');

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok'));

		self::assertSame(0, $this->recorder->enable_calls);
		self::assertSame(0, $this->recorder->dump_calls);
		self::assertCount(0, Storage::disk(FileSystem::PROFILING)->allFiles());
	}

	public function testNoOpWhenExtensionUnavailable(): void
	{
		config(['features.memory-profiler' => true]);
		$this->recorder->available = false;
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo', 'GET');

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok'));

		self::assertSame(0, $this->recorder->enable_calls);
		self::assertSame(0, $this->recorder->dump_calls);
		self::assertCount(0, Storage::disk(FileSystem::PROFILING)->allFiles());
	}

	public function testCapturesTraceWhenEnabledAndAvailable(): void
	{
		config(['features.memory-profiler' => true]);
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo/bar', 'POST');

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok', 201));

		self::assertSame(1, $this->recorder->enable_calls);
		self::assertSame(1, $this->recorder->disable_calls);
		self::assertSame(1, $this->recorder->dump_calls);

		$files = Storage::disk(FileSystem::PROFILING)->allFiles();
		self::assertCount(2, $files);

		$json_file = collect($files)->first(fn ($f) => str_ends_with($f, '.json'));
		self::assertNotNull($json_file);
		$meta = json_decode(Storage::disk(FileSystem::PROFILING)->get($json_file), true);
		self::assertSame('POST', $meta['method']);
		self::assertSame('foo/bar', $meta['path']);
		self::assertSame(201, $meta['status_code']);
	}

	public function testDumpFailureIsLoggedAndDoesNotThrow(): void
	{
		config(['features.memory-profiler' => true]);
		$this->recorder->throw_on_dump = true;
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo', 'GET');

		Log::shouldReceive('error')->once()->with('memory_profiler.dump_failed', \Mockery::type('array'));

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok'));

		self::assertCount(0, Storage::disk(FileSystem::PROFILING)->allFiles());
	}
}
