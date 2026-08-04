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

use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class TagTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testFromCreatesMissingTags(): void
	{
		$tags = Tag::from(['sunset', 'beach']);

		self::assertCount(2, $tags);
		self::assertEqualsCanonicalizing(['sunset', 'beach'], $tags->pluck('name')->all());
	}

	public function testFromReusesExistingTagsInsteadOfDuplicating(): void
	{
		// Note: matching is case-insensitive at the DB level under MySQL/MariaDB
		// (the production target's default collation), but SQLite - used here -
		// compares strings case-sensitively, so this only exercises the
		// exact-match reuse path; the case-insensitive fallback comparison in
		// Tag::from() only ever affects which *missing* tags get inserted.
		$existing = Tag::create(['name' => 'sunset']);

		$tags = Tag::from(['sunset', 'beach']);

		self::assertCount(2, $tags);
		self::assertEquals(1, Tag::where('name', 'sunset')->count());
		self::assertTrue($tags->contains('id', $existing->id));
	}

	public function testFromTrimsWhitespaceAndDropsEmptyEntries(): void
	{
		$tags = Tag::from(['  sunset  ', '', '   ']);

		self::assertCount(1, $tags);
		self::assertEquals('sunset', $tags->first()->name);
	}

	public function testFromWithEmptyArrayReturnsEmptyCollection(): void
	{
		$tags = Tag::from([]);

		self::assertCount(0, $tags);
	}
}
