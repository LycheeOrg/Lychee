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

use App\Constants\AccessPermissionConstants as APC;
use App\DTO\EffectiveAccessPermission;
use App\Models\AccessPermission;
use App\Models\BaseAlbumImpl;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Support\Collection;
use Tests\AbstractTestCase;

/**
 * Unit tests for BaseAlbumImpl::current_user_permissions() filter/delegate/
 * null-short-circuit logic, using in-memory relations (no DB access).
 */
class BaseAlbumImplCurrentUserPermissionsTest extends AbstractTestCase
{
	private function makeUser(int $id, array $group_ids = []): User
	{
		$user = new User();
		$user->id = $id;
		$user->setRelation('user_groups', new Collection(array_map(
			function (int $group_id): UserGroup {
				$group = new UserGroup();
				$group->id = $group_id;

				return $group;
			},
			$group_ids
		)));

		return $user;
	}

	private function makeAlbum(Collection $access_permissions): BaseAlbumImpl
	{
		$album = new BaseAlbumImpl();
		$album->setRelation(APC::ACCESS_PERMISSIONS, $access_permissions);

		return $album;
	}

	public function testGuestReturnsNullWithoutEvaluatingAnyRow(): void
	{
		$album = $this->makeAlbum(new Collection([
			new AccessPermission(['user_id' => 1, 'grants_download' => true]),
		]));

		self::assertNull($album->current_user_permissions());
	}

	public function testNoMatchingRowReturnsNull(): void
	{
		$user = $this->makeUser(1, [10]);
		$album = $this->makeAlbum(new Collection([
			new AccessPermission(['user_id' => 2, 'grants_download' => true]),
			new AccessPermission(['user_group_id' => 20, 'grants_download' => true]),
		]));

		$this->actingAs($user);

		self::assertNull($album->current_user_permissions());
	}

	public function testDirectShareOnlyIsReturnedUnaffected(): void
	{
		$user = $this->makeUser(1, []);
		$album = $this->makeAlbum(new Collection([
			new AccessPermission([
				'user_id' => 1,
				'grants_download' => true,
				'grants_upload' => false,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => false,
			]),
		]));

		$this->actingAs($user);

		$merged = $album->current_user_permissions();
		self::assertInstanceOf(EffectiveAccessPermission::class, $merged);
		self::assertTrue($merged->grants_download);
		self::assertFalse($merged->grants_upload);
	}

	public function testSingleGroupOnlyIsReturnedUnaffected(): void
	{
		$user = $this->makeUser(1, [10]);
		$album = $this->makeAlbum(new Collection([
			new AccessPermission([
				'user_group_id' => 10,
				'grants_download' => true,
				'grants_upload' => true,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => false,
			]),
		]));

		$this->actingAs($user);

		$merged = $album->current_user_permissions();
		self::assertInstanceOf(EffectiveAccessPermission::class, $merged);
		self::assertTrue($merged->grants_download);
		self::assertTrue($merged->grants_upload);
	}

	/**
	 * Reproduces the reported bug: a user in two groups ("All" = View only,
	 * "Support_VIP" = View+Access+Download) must receive the union of both,
	 * regardless of row order.
	 */
	public function testUserInMultipleGroupsReceivesUnionOfGrants(): void
	{
		$user = $this->makeUser(1, [10, 20]);
		$album = $this->makeAlbum(new Collection([
			new AccessPermission([
				'user_group_id' => 10, // "All" - View only
				'grants_download' => false,
				'grants_upload' => false,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => false,
			]),
			new AccessPermission([
				'user_group_id' => 20, // "Support_VIP" - View, Access, Download
				'grants_download' => true,
				'grants_upload' => false,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => true,
			]),
		]));

		$this->actingAs($user);

		$merged = $album->current_user_permissions();
		self::assertInstanceOf(EffectiveAccessPermission::class, $merged);
		self::assertTrue($merged->grants_download);
		self::assertTrue($merged->grants_full_photo_access);
	}

	public function testUserInMultipleGroupsUnaffectedByRowOrder(): void
	{
		$user = $this->makeUser(1, [10, 20]);
		$album = $this->makeAlbum(new Collection([
			new AccessPermission([
				'user_group_id' => 20, // "Support_VIP" created first this time
				'grants_download' => true,
				'grants_upload' => false,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => true,
			]),
			new AccessPermission([
				'user_group_id' => 10, // "All"
				'grants_download' => false,
				'grants_upload' => false,
				'grants_edit' => false,
				'grants_delete' => false,
				'grants_full_photo_access' => false,
			]),
		]));

		$this->actingAs($user);

		$merged = $album->current_user_permissions();
		self::assertInstanceOf(EffectiveAccessPermission::class, $merged);
		self::assertTrue($merged->grants_download);
		self::assertTrue($merged->grants_full_photo_access);
	}
}
