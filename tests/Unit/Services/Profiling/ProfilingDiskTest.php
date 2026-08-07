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
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

class ProfilingDiskTest extends AbstractTestCase
{
	public function testDiskIsLocalAndPrivate(): void
	{
		$disk = Storage::disk(FileSystem::PROFILING);

		self::assertStringEndsWith('profiling', rtrim($disk->path(''), '/'));
	}

	public function testDiskCanWriteAndReadBack(): void
	{
		$disk = Storage::disk(FileSystem::PROFILING);

		$disk->put('unit-test-marker.txt', 'hello');
		self::assertTrue($disk->exists('unit-test-marker.txt'));
		self::assertSame('hello', $disk->get('unit-test-marker.txt'));
		$disk->delete('unit-test-marker.txt');
	}
}
