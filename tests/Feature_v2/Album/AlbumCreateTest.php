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

use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Models\Statistics;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumCreateTest extends BaseApiWithDataTest
{
	public function testCreateAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album', [
			'parent_id' => null,
			'title' => 'test',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		$this->assertForbidden($response);
	}

	public function testCreateAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();
		$this->assertEquals(1, Statistics::where('album_id', $new_album_id)->count());

		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($new_album_id);
	}

	public function testCreateAlbumAuthorizedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();

		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($new_album_id);
		$this->assertEquals(1, Statistics::where('album_id', $new_album_id)->count());
	}

	/**
	 * album1 has both a user-based permission (perm1, for userMayUpload2) and a
	 * user-group-based permission (perm11, for group1). When default_album_protection
	 * is set to "inherit", creating a sub-album must copy both permissions onto the
	 * new album via AccessPermission::ofAccessPermission()/replicate().
	 *
	 * Regression test: replicate() used to also copy the `user_id_unique_key` /
	 * `user_group_id_unique_key` generated columns, which the DB rejects on
	 * insert, making this scenario throw instead of creating the sub-album.
	 */
	public function testCreateAlbumInheritsPermissionsFromParent(): void
	{
		Configs::set('default_album_protection', 'inherit');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $this->album1->id,
			'title' => 'test',
		]);
		$this->assertOk($response);
		$new_album_id = $response->getOriginalContent();

		$copied_permissions = AccessPermission::where(APC::BASE_ALBUM_ID, '=', $new_album_id)->get();

		$user_permission = $copied_permissions->firstWhere(APC::USER_ID, $this->userMayUpload2->id);
		self::assertNotNull($user_permission);
		self::assertTrue($user_permission->grants_edit);
		self::assertTrue($user_permission->grants_delete);
		self::assertTrue($user_permission->grants_upload);
		self::assertTrue($user_permission->grants_download);
		self::assertTrue($user_permission->grants_full_photo_access);

		$group_permission = $copied_permissions->firstWhere(APC::USER_GROUP_ID, $this->group1->id);
		self::assertNotNull($group_permission);
		self::assertTrue($group_permission->grants_edit);
		self::assertTrue($group_permission->grants_delete);
		self::assertTrue($group_permission->grants_upload);
		self::assertTrue($group_permission->grants_download);
		self::assertTrue($group_permission->grants_full_photo_access);
	}
}