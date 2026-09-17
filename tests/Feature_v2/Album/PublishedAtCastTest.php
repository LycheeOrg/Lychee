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

namespace Tests\Feature_v2\Album;

use App\Models\Album;
use Illuminate\Support\Carbon;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Covers Feature 068's `published_at`/`published_at_orig_tz` cast wiring
 * (FR-068-10, FR-068-11) — `BaseAlbumImpl::$published_at` now round-trips a
 * timezone-aware instant via `DateTimeWithTimezoneCast`, mirroring
 * `Photo::taken_at`'s established pattern exactly.
 */
class PublishedAtCastTest extends BaseApiWithDataTest
{
	public function testRoundTripsTimezoneAwareInstant(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		$instant = Carbon::parse('2026-09-17T10:00:00', 'Europe/Paris');
		$album->published_at = $instant;
		$album->save();

		$fresh = Album::query()->findOrFail($album->id);
		self::assertNotNull($fresh->published_at);
		self::assertTrue($instant->eq($fresh->published_at));
		self::assertSame('Europe/Paris', $fresh->published_at->getTimezone()->getName());
	}

	public function testNullPublishedAtRoundTripsToNull(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

		self::assertNull($album->fresh()->published_at);
	}

	public function testReassigningSameInstantIsNotDirty(): void
	{
		// Exercises DateTimeWithTimezoneCast::compare() - a Q-068-01-adjacent
		// regression guard: reassigning an unchanged instant must not be
		// spuriously flagged as a change (the exact bug class documented in
		// [[project_datetimewithtimezonecast_dirty_check_bug]], already
		// fixed at the cast level and reused here unmodified).
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$instant = Carbon::parse('2026-09-17T10:00:00', 'UTC');
		$album->published_at = $instant;
		$album->save();

		$fresh = Album::query()->findOrFail($album->id);
		$fresh->published_at = $fresh->published_at;
		self::assertFalse($fresh->isDirty('published_at'));
	}

	public function testClearingPublishedAtSetsBothColumnsNull(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$album->published_at = Carbon::parse('2026-09-17T10:00:00', 'UTC');
		$album->save();

		$album->published_at = null;
		$album->save();

		$fresh = Album::query()->findOrFail($album->id);
		self::assertNull($fresh->published_at);
		self::assertNull($fresh->published_at_orig_tz);
	}
}
