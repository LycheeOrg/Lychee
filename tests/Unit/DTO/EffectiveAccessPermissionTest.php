<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\DTO;

use App\DTO\EffectiveAccessPermission;
use App\Models\AccessPermission;
use Illuminate\Support\Collection;
use Tests\AbstractTestCase;

/**
 * Unit tests for EffectiveAccessPermission::merge().
 *
 * Regression coverage for the bug where a user belonging to multiple groups
 * only received the grants of whichever group's AccessPermission row was
 * created first, instead of the union of every applicable grant.
 */
class EffectiveAccessPermissionTest extends AbstractTestCase
{
	public function testMergeOfEmptyCollectionYieldsNoGrants(): void
	{
		$merged = EffectiveAccessPermission::merge(new Collection());

		self::assertFalse($merged->grants_full_photo_access);
		self::assertFalse($merged->grants_download);
		self::assertFalse($merged->grants_upload);
		self::assertFalse($merged->grants_edit);
		self::assertFalse($merged->grants_delete);
	}

	/**
	 * S-048-01: low-grant group row created before a high-grant group row —
	 * the merged result must expose the union of both.
	 */
	public function testMergeUnionsLowThenHighGrantRows(): void
	{
		$low = new AccessPermission([
			'grants_full_photo_access' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
		]);
		$high = new AccessPermission([
			'grants_full_photo_access' => true,
			'grants_download' => true,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
		]);

		$merged = EffectiveAccessPermission::merge(new Collection([$low, $high]));

		self::assertTrue($merged->grants_full_photo_access);
		self::assertTrue($merged->grants_download);
		self::assertFalse($merged->grants_upload);
		self::assertFalse($merged->grants_edit);
		self::assertFalse($merged->grants_delete);
	}

	/**
	 * S-048-02: identical rows in reversed order must yield the identical
	 * merged result (order-independence).
	 */
	public function testMergeIsOrderIndependent(): void
	{
		$low = new AccessPermission([
			'grants_full_photo_access' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
		]);
		$high = new AccessPermission([
			'grants_full_photo_access' => true,
			'grants_download' => true,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
		]);

		$mergedLowFirst = EffectiveAccessPermission::merge(new Collection([$low, $high]));
		$mergedHighFirst = EffectiveAccessPermission::merge(new Collection([$high, $low]));

		self::assertEquals($mergedLowFirst, $mergedHighFirst);
		self::assertTrue($mergedHighFirst->grants_download);
	}

	/**
	 * S-048-03 (Q-048-01, Option A): a direct-user row and a group row are
	 * merged with no precedence — most permissive always wins.
	 */
	public function testMergeUnionsDirectUserRowWithGroupRow(): void
	{
		$directUser = new AccessPermission([
			'user_id' => 1,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_full_photo_access' => false,
		]);
		$group = new AccessPermission([
			'user_group_id' => 42,
			'grants_download' => true,
			'grants_upload' => true,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_full_photo_access' => false,
		]);

		$merged = EffectiveAccessPermission::merge(new Collection([$directUser, $group]));

		self::assertTrue($merged->grants_download);
		self::assertTrue($merged->grants_upload);
		self::assertFalse($merged->grants_edit);
		self::assertFalse($merged->grants_delete);
	}

	public function testMergeOfSingleRowReflectsItsOwnGrants(): void
	{
		$permission = new AccessPermission([
			'grants_full_photo_access' => true,
			'grants_download' => false,
			'grants_upload' => true,
			'grants_edit' => false,
			'grants_delete' => true,
		]);

		$merged = EffectiveAccessPermission::merge(new Collection([$permission]));

		self::assertTrue($merged->grants_full_photo_access);
		self::assertFalse($merged->grants_download);
		self::assertTrue($merged->grants_upload);
		self::assertFalse($merged->grants_edit);
		self::assertTrue($merged->grants_delete);
	}
}
