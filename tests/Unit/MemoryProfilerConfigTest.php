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

namespace Tests\Unit;

use App\Assets\Features;
use Tests\AbstractTestCase;

class MemoryProfilerConfigTest extends AbstractTestCase
{
	public function testDisabledByDefault(): void
	{
		self::assertFalse(Features::active('memory-profiler'));
	}

	public function testDefaultMaxTraces(): void
	{
		self::assertSame(200, config('features.memory-profiler-max-traces'));
	}

	public function testDefaultSpxKeyIsUnset(): void
	{
		self::assertNull(config('features.memory-profiler-spx-key'));
	}
}
