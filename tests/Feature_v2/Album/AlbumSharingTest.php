<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

use App\Models\AccessPermission;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/*
 * Regression tests for https://github.com/LycheeOrg/Lychee/issues/3586
 * "albums that are shared via both the new user group feature and also made public turn up twice for logged in users.
 * When a user is member of multiple groups, more albums turn up."
 *
 * Expected behavior is that albums are only listed once, regardless of how they are shared.
 * Expected behavior is that the access for that album via group are taken as max.
 * Consiser an album A.
 * User 1 is member of group 1 and group 2.
 * Group 1 has access to album A - with download right.
 * Group 2 has access to album A - without download rights.
 * User 1 should only see album A once and has download rights.
 *
 * This tests ensure that:
 * - If a user is member of two groups that share the same album, it only appears once in the list of shared albums.
 * - If an album is public and shared with a user, it only appears once in the list of shared albums.
 * - If an album is public and shared with a user via group, it only appears once in the list of shared albums.
 * - If an album is shared via member and via group, it only appears once in the list of shared albums.
 */
class AlbumSharingTest extends BaseApiWithDataTest
{
	/**
	 * Ensure that if a user is member of two groups that share the same album,
	 * the album is only listed once.
	 *
	 * @return void
	 */
	public function testUserInTwoGroupsWithSameSharedAlbum(): void
	{
		// Add UserLocked to group 1 and group 2.
		$this->userLocked->user_groups()->attach($this->group1);
		$this->userLocked->user_groups()->attach($this->group2);

		// Setup group 2 to have access to album1.
		// This is the album that is shared with group1.
		AccessPermission::factory()
			->for_user_group($this->group2)
			->for_album($this->album1)
			->visible()
			->grants_edit()
			->grants_delete()
			->grants_upload()
			->grants_download()
			->grants_full_photo()
			->create();

		// Now userLocked should be able to see album1.
		$response = $this->actingAs($this->userLocked)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album1->id);
	}

	/**
	 * Ensure that if an album is public and shared with a user,
	 * it only appears once in the list of albums.
	 *
	 * @return void
	 */
	public function testUserSharedWithPublic(): void
	{
		// UserMayUpload1 should be able to see album4 (it is public).
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album4->id);

		// Give access to album4 to userMayUpload1.
		// This is the album that is public.
		AccessPermission::factory()
			->for_user($this->userMayUpload1)
			->for_album($this->album4)
			->visible()
			->grants_edit()
			->grants_delete()
			->grants_upload()
			->grants_download()
			->grants_full_photo()
			->create();

		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album4->id,
			'user_id' => $this->userMayUpload1->id,
		]);
		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album4->id,
			'user_id' => null,
			'user_group_id' => null,
		]);

		// Now userMayUpload1 should be able to see album4.
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album4->id);
	}

	/**
	 * Ensure that if an album is public and shared with a user via group,
	 * it only appears once in the list of albums.
	 */
	public function testUserGroupWithPublic(): void
	{
		// UserMayUpload1 should be able to see album4 (it is public).
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album4->id);

		$this->userMayUpload1->user_groups()->attach($this->group1);
		$this->assertDatabaseHas('users_user_groups', [
			'user_id' => $this->userMayUpload1->id,
			'user_group_id' => $this->group1->id,
		]);

		// Give access to album4 to group1.
		// This is the album that is public.
		AccessPermission::factory()
			->for_user_group($this->group1)
			->for_album($this->album4)
			->visible()
			->grants_edit()
			->grants_delete()
			->grants_upload()
			->grants_download()
			->grants_full_photo()
			->create();

		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album4->id,
			'user_group_id' => $this->group1->id,
		]);
		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album4->id,
			'user_id' => null,
			'user_group_id' => null,
		]);

		// Now userMayUpload1 should be able to see album4.
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album4->id);
	}

	/**
	 * Ensure that if an album is shared directly and shared with a user via group,
	 * it only appears once in the list of albums.
	 */
	public function testUserGroupWithShare(): void
	{
		// Remove the public permission from album4.
		// This is to simplify the tests.
		$this->perm4->delete();

		// UserMayUpload1 should be able to see album4 (it is public).
		$response = $this->actingAs($this->userMayUpload2)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album1->id);

		// Add userMayUplaod2 to group1.
		$this->userMayUpload2->user_groups()->attach($this->group1);
		$this->assertDatabaseHas('users_user_groups', [
			'user_id' => $this->userMayUpload2->id,
			'user_group_id' => $this->group1->id,
		]);

		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album1->id,
			'user_group_id' => $this->group1->id,
		]);
		$this->assertDatabaseHas('access_permissions', [
			'base_album_id' => $this->album1->id,
			'user_id' => $this->userMayUpload2->id,
		]);

		// Now userMayUpload2 should be able to see album1 only once.
		$response = $this->actingAs($this->userMayUpload2)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [
			],
		]);

		// Behaviour we do not want.
		$response->assertJsonCount(1, 'shared_albums');
		$response->assertJsonPath('shared_albums.0.id', $this->album1->id);
	}

	public function testSharedViewAsAdmin(): void
	{
		$this->userWithGroup1->may_administrate = true;
		$this->userWithGroup1->save();

		$response = $this->actingAs($this->userWithGroup1)->getJson('Albums');

		$ids = array_map(fn ($a) => $a['id'], $response->json()['shared_albums']);
		$this->assertCount(5, $ids);
		$this->assertContains($this->album1->id, $ids);
		$this->assertContains($this->album2->id, $ids);
		$this->assertContains($this->album3->id, $ids);
		$this->assertContains($this->album4->id, $ids);
		$this->assertContains($this->album5->id, $ids);
	}
}