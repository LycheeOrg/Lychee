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

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: sharing depends on `may_share`, not on `may_upload`
 * (FR-118-02 … 05, S-118-02 … 05, S-118-16).
 */
class ShareRightTest extends BaseApiWithDataTest
{
	private User $sharer;
	private Album $sharer_album;

	public function setUp(): void
	{
		parent::setUp();
		$this->sharer = User::factory()->may_share()->create();
		$this->sharer_album = Album::factory()->as_root()->owned_by($this->sharer)->create();
	}

	/**
	 * `userMayUpload1` with the share right withdrawn directly in the database:
	 * the policies read `may_share` alone.
	 */
	private function uploaderWithoutShare(): User
	{
		DB::table('users')->where('id', '=', $this->userMayUpload1->id)->update(['may_share' => false]);

		return $this->userMayUpload1->fresh();
	}

	private function sharePayload(string $album_id, int $user_id): array
	{
		return [
			'user_ids' => [$user_id],
			'group_ids' => [],
			'album_ids' => [$album_id],
			'grants_edit' => false,
			'grants_move' => false,
			'grants_delete' => false,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
		];
	}

	private function protectionPayload(string $album_id): array
	{
		return [
			'album_id' => $album_id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		];
	}

	public function testSharerManagesSharesOfOwnAlbum(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->sharePayload($this->sharer_album->id, $this->userMayUpload2->id));
		$this->assertOk($response);

		$response = $this->actingAs($this->sharer)->getJsonWithData('Sharing', ['album_id' => $this->sharer_album->id]);
		$this->assertOk($response);

		$response = $this->actingAs($this->sharer)->getJson('Sharing::all');
		$this->assertOk($response);

		$perm = AccessPermission::query()->where('base_album_id', '=', $this->sharer_album->id)->where('user_id', '=', $this->userMayUpload2->id)->firstOrFail();
		$response = $this->actingAs($this->sharer)->patchJson('Sharing', [
			'perm_id' => $perm->id,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_download' => false,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
			'grants_move' => false,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->sharer)->putJson('Sharing', ['album_id' => $this->sharer_album->id, 'shall_override' => false]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->sharer)->deleteJson('Sharing', ['perm_id' => $perm->id]);
		$this->assertNoContent($response);
	}

	public function testSharerChangesVisibilityOfOwnAlbum(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Album::updateProtectionPolicy', $this->protectionPayload($this->sharer_album->id));
		$this->assertCreated($response);
	}

	public function testSharerTransfersOwnAlbum(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Album::transfer', [
			'album_id' => $this->sharer_album->id,
			'user_id' => $this->userMayUpload2->id,
		]);
		$this->assertNoContent($response);
	}

	public function testSharerListsUsersAndGroups(): void
	{
		$response = $this->actingAs($this->sharer)->getJson('Users');
		$this->assertOk($response);

		self::assertTrue(Gate::forUser($this->sharer)->check(UserGroupPolicy::CAN_LIST, [UserGroup::class]));
	}

	public function testSharerRootRights(): void
	{
		$response = $this->actingAs($this->sharer)->getJson('Auth::rights');
		$this->assertOk($response);
		$response->assertJson(['root_album' => ['can_share_with_users' => true, 'can_upload' => false, 'can_edit' => false]]);
	}

	public function testSharerWithoutAlbumSeesEmptySharingPage(): void
	{
		$sharer_without_album = User::factory()->may_share()->create();

		$response = $this->actingAs($sharer_without_album)->getJson('Auth::rights');
		$this->assertOk($response);
		$response->assertJson(['root_album' => ['can_share_with_users' => true]]);

		$response = $this->actingAs($sharer_without_album)->getJson('Sharing::all');
		$this->assertOk($response);
		$response->assertJsonCount(0);
	}

	public function testSharerCannotShareForeignAlbum(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->sharePayload($this->album1->id, $this->userMayUpload2->id));
		$this->assertForbidden($response);

		$response = $this->actingAs($this->sharer)->postJson('Album::updateProtectionPolicy', $this->protectionPayload($this->album1->id));
		$this->assertForbidden($response);
	}

	public function testUserWithoutShareRightIsRefused(): void
	{
		$uploader = $this->uploaderWithoutShare();

		$response = $this->actingAs($uploader)->postJson('Sharing', $this->sharePayload($this->album1->id, $this->userMayUpload2->id));
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->getJson('Sharing::all');
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->patchJson('Sharing', [
			'perm_id' => $this->perm1->id,
			'grants_edit' => false,
			'grants_delete' => false,
			'grants_download' => false,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
			'grants_move' => false,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->deleteJson('Sharing', ['perm_id' => $this->perm1->id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->putJson('Sharing', ['album_id' => $this->album1->id, 'shall_override' => false]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album::updateProtectionPolicy', $this->protectionPayload($this->album1->id));
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album::transfer', ['album_id' => $this->album1->id, 'user_id' => $this->userMayUpload2->id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->getJson('Users');
		$this->assertForbidden($response);

		self::assertFalse(Gate::forUser($uploader)->check(UserGroupPolicy::CAN_LIST, [UserGroup::class]));

		$response = $this->actingAs($uploader)->getJson('Auth::rights');
		$this->assertOk($response);
		$response->assertJson(['root_album' => ['can_share_with_users' => false]]);
	}

	public function testOwnerWithoutAnyRightIsRefused(): void
	{
		// `userNoUpload` owns album3 but has no share right.
		$response = $this->actingAs($this->userNoUpload)->postJson('Album::updateProtectionPolicy', $this->protectionPayload($this->album3->id));
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Album::transfer', ['album_id' => $this->album3->id, 'user_id' => $this->userMayUpload2->id]);
		$this->assertForbidden($response);
	}
}
