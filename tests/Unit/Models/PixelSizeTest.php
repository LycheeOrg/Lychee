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

namespace Tests\Unit\Models;

use App\Models\PixelSize;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class PixelSizeTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testScopeActiveOnlyReturnsActiveSizes(): void
	{
		$active = PixelSize::factory()->create(['is_active' => true]);
		PixelSize::factory()->create(['is_active' => false]);

		$results = PixelSize::query()->active()->get();

		self::assertCount(1, $results);
		self::assertEquals($active->id, $results->first()->id);
	}
}
