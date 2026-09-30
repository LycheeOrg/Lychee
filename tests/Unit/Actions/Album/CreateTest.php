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

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\Create;
use App\Exceptions\ConflictingPropertyException;
use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Sleep;
use Tests\AbstractTestCase;

class CreateTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCreateWaitsForTreeLockAndFailsOnTimeout(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$albums_before = Album::query()->count();

		$held_lock = Cache::lock(Create::TREE_LOCK_KEY, 600);
		self::assertTrue($held_lock->get());
		Sleep::fake(syncWithCarbon: true);

		try {
			(new Create($user->id))->create('blocked', $parent);
			self::fail('Expected ConflictingPropertyException');
		} catch (ConflictingPropertyException) {
			self::assertSame($albums_before, Album::query()->count());
		} finally {
			$held_lock->release();
		}
	}

	public function testSiblingsGetDisjointTreeBoundsAndLockIsReleased(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		$create = new Create($user->id);
		$first = $create->create('first', $parent);
		$second = $create->create('second', $parent);

		self::assertTrue($second->_lft > $first->_rgt);
		self::assertTrue(Cache::lock(Create::TREE_LOCK_KEY, 1)->get());
	}
}
