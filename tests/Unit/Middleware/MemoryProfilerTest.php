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
use App\Services\Profiling\SpxRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

/**
 * Fake recorder so these tests never require the real `spx` PECL extension
 * to be installed (see spec.md NFR-053-05 / plan.md risk notes).
 */
class FakeSpxRecorder extends SpxRecorder
{
	public int $start_calls = 0;
	public int $stop_calls = 0;
	public bool $available = true;
	public ?string $next_report_key = 'spx-full-fake-key';

	public function isAvailable(): bool
	{
		return $this->available;
	}

	public function start(): void
	{
		$this->start_calls++;
	}

	public function stop(): ?string
	{
		$this->stop_calls++;

		return $this->next_report_key;
	}
}

class MemoryProfilerTest extends AbstractTestCase
{
	private FakeSpxRecorder $recorder;

	protected function setUp(): void
	{
		parent::setUp();
		$this->recorder = new FakeSpxRecorder();

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

		self::assertSame(0, $this->recorder->start_calls);
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

		self::assertSame(0, $this->recorder->start_calls);
		self::assertCount(0, Storage::disk(FileSystem::PROFILING)->allFiles());
	}

	public function testCapturesTraceWhenEnabledAndAvailable(): void
	{
		config(['features.memory-profiler' => true]);
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo/bar', 'POST');

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok', 201));

		self::assertSame(1, $this->recorder->start_calls);
		self::assertSame(1, $this->recorder->stop_calls);

		$files = Storage::disk(FileSystem::PROFILING)->allFiles();
		self::assertCount(1, $files);
		self::assertStringStartsWith('lychee-', $files[0]);

		$meta = json_decode(Storage::disk(FileSystem::PROFILING)->get($files[0]), true);
		self::assertSame('POST', $meta['method']);
		self::assertSame('foo/bar', $meta['path']);
		self::assertSame(201, $meta['status_code']);
		self::assertSame('spx-full-fake-key', $meta['spx_report_key']);
	}

	public function testCapturesTraceWithNullReportKeyWhenSpxDidNotProduceOne(): void
	{
		config(['features.memory-profiler' => true]);
		$this->recorder->next_report_key = null;
		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo', 'GET');

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok'));

		$files = Storage::disk(FileSystem::PROFILING)->allFiles();
		self::assertCount(1, $files);
		$meta = json_decode(Storage::disk(FileSystem::PROFILING)->get($files[0]), true);
		self::assertNull($meta['spx_report_key']);
	}

	public function testDumpFailureIsLoggedAndDoesNotThrow(): void
	{
		config(['features.memory-profiler' => true]);

		$broken_disk = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
		$broken_disk->shouldReceive('put')->andThrow(new \RuntimeException('simulated disk failure'));
		Storage::set(FileSystem::PROFILING, $broken_disk);

		$middleware = new MemoryProfiler($this->recorder);
		$request = Request::create('/foo', 'GET');

		Log::shouldReceive('error')->once()->with('memory_profiler.dump_failed', \Mockery::type('array'));

		$middleware->handle($request, fn ($req) => new Response('ok'));
		$middleware->terminate($request, new Response('ok'));

		Storage::forgetDisk(FileSystem::PROFILING);
	}
}
