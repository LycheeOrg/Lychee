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

namespace Tests\Feature_v3\Album;

use Illuminate\Support\Facades\Auth;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /api/v3/Albums::accessPermissions`.
 *
 * Reuses the v2 fixture graph (see
 * {@see \Tests\Feature_v2\Base\BaseApiWithDataTest}): album1/subAlbum1
 * (userMayUpload1) with perm1 (user share to userMayUpload2, all grants) and
 * perm11 (group share to group1, all grants); album2/subAlbum2
 * (userMayUpload2, no permissions); album3 (userNoUpload, no permissions);
 * album4/subAlbum4 (userLocked) with perm4/perm44 (public, no user/group);
 * album5 (admin, empty, no permissions).
 */
class AlbumAccessPermissionListTest extends BaseApiWithDataTest
{
	public function testGuestUnauthorized(): void
	{
		Auth::logout();
		$response = $this->getJsonV3('Albums::accessPermissions');
		$this->assertUnauthorized($response);
	}

	public function testNonUploaderForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJsonV3('Albums::accessPermissions');
		$this->assertForbidden($response);
	}

	public function testOwnerSeesOnlyOwnAlbums(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		self::assertEqualsCanonicalizing(
			[$this->album1->id, $this->album1->id, $this->subAlbum1->id],
			$json['album_ids']
		);
		self::assertNotContains($this->album2->id, $json['album_ids']);
	}

	public function testOwnerDoesNotSeeAlbumSharedWithThemButNotOwned(): void
	{
		// userMayUpload2 is granted access to album1 via perm1, but does not own it.
		$response = $this->actingAs($this->userMayUpload2)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		self::assertNotContains($this->album1->id, $json['album_ids']);
		self::assertEqualsCanonicalizing([$this->album2->id, $this->subAlbum2->id], $json['album_ids']);
		self::assertSame([null, null], $json['permission_ids']);
	}

	public function testAdminSeesAllAlbumsAndPermissionRows(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		// album1 appears twice: once for perm1 (user), once for perm11 (group).
		self::assertCount(2, array_keys($json['album_ids'], $this->album1->id, true));

		// Every other real album appears exactly once (no permission, or a public-only permission).
		foreach ([
			$this->subAlbum1->id,
			$this->album2->id,
			$this->subAlbum2->id,
			$this->album3->id,
			$this->album4->id,
			$this->subAlbum4->id,
			$this->album5->id,
		] as $album_id) {
			self::assertCount(1, array_keys($json['album_ids'], $album_id, true), "album {$album_id} should appear exactly once");
		}
	}

	public function testPermissionRowFieldsMatchUserPermission(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		$idx = array_search($this->perm1->id, $json['permission_ids'], true);
		self::assertNotFalse($idx);
		self::assertSame($this->album1->id, $json['album_ids'][$idx]);
		self::assertSame($this->userMayUpload2->id, $json['user_ids'][$idx]);
		self::assertSame($this->userMayUpload2->name, $json['user_names'][$idx]);
		self::assertNull($json['group_ids'][$idx]);
		self::assertTrue($json['grants_full_photo_accesses'][$idx]);
		self::assertTrue($json['grants_downloads'][$idx]);
		self::assertTrue($json['grants_uploads'][$idx]);
		self::assertTrue($json['grants_edits'][$idx]);
		self::assertTrue($json['grants_deletes'][$idx]);
	}

	public function testPermissionRowFieldsMatchGroupPermission(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		$idx = array_search($this->perm11->id, $json['permission_ids'], true);
		self::assertNotFalse($idx);
		self::assertSame($this->album1->id, $json['album_ids'][$idx]);
		self::assertNull($json['user_ids'][$idx]);
		self::assertSame($this->group1->id, $json['group_ids'][$idx]);
		self::assertSame($this->group1->name, $json['group_names'][$idx]);
	}

	public function testAlbumWithoutPermissionsHasNullFields(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		$idx = array_search($this->album5->id, $json['album_ids'], true);
		self::assertNotFalse($idx);
		self::assertNull($json['permission_ids'][$idx]);
		self::assertNull($json['user_ids'][$idx]);
		self::assertNull($json['group_ids'][$idx]);
		self::assertNull($json['grants_full_photo_accesses'][$idx]);
	}

	public function testPublicOnlyPermissionIsExcludedButAlbumStillAppears(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		// perm4/perm44 are public (no user_id/group_id) and must never surface here.
		self::assertNotContains($this->perm4->id, $json['permission_ids']);
		self::assertNotContains($this->perm44->id, $json['permission_ids']);

		$idx = array_search($this->album4->id, $json['album_ids'], true);
		self::assertNotFalse($idx);
		self::assertNull($json['permission_ids'][$idx]);
	}

	public function testOwnerNamesArePopulated(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Albums::accessPermissions');
		$response->assertOk();
		$json = $response->json();

		$idx = array_search($this->album1->id, $json['album_ids'], true);
		self::assertNotFalse($idx);
		self::assertSame($this->userMayUpload1->id, $json['owner_ids'][$idx]);
		self::assertSame($this->userMayUpload1->name, $json['owner_names'][$idx]);
	}
}
