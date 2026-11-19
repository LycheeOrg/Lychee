<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\SyncRecursiveNsfw;
use Tests\AbstractTestCase;

/**
 * Feature 103 (FR-103-11): the nested-set ranges a recursive NSFW sync writes.
 */
class SyncRecursiveNsfwTest extends AbstractTestCase
{
	public function testNoRanges(): void
	{
		self::assertSame([], SyncRecursiveNsfw::outermostRanges([]));
	}

	public function testDisjointRangesAreKept(): void
	{
		self::assertSame([[1, 4], [5, 8]], SyncRecursiveNsfw::outermostRanges([[1, 4], [5, 8]]));
	}

	public function testNestedRangesCollapseIntoTheOutermost(): void
	{
		self::assertSame([[1, 10]], SyncRecursiveNsfw::outermostRanges([[1, 10], [2, 3], [4, 9], [5, 6]]));
	}

	public function testRangeAfterANestedOneIsKept(): void
	{
		self::assertSame([[1, 4], [5, 6]], SyncRecursiveNsfw::outermostRanges([[1, 4], [2, 3], [5, 6]]));
	}

	public function testUnsortedInput(): void
	{
		self::assertSame([[1, 10], [11, 12]], SyncRecursiveNsfw::outermostRanges([[5, 6], [11, 12], [1, 10]]));
	}
}
