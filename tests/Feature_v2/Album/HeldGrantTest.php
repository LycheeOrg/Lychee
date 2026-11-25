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
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: a sharer hands out only the grants they hold
 * (FR-118-07, S-118-17, S-118-18), and keeps the ones given before
 * losing the right (Q-118-09, S-118-22).
 */
class HeldGrantTest extends BaseApiWithDataTest
{
	private User $sharer;
	private User $sharer_editor;
	private Album $sharer_album;
	private Album $sharer_album_bis;
	private Album $editor_album;

	public function setUp(): void
	{
		parent::setUp();
		$this->sharer = User::factory()->may_share()->create();
		$this->sharer_editor = User::factory()->may_share()->may_edit()->create();
		$this->sharer_album = Album::factory()->as_root()->owned_by($this->sharer)->create();
		$this->sharer_album_bis = Album::factory()->as_root()->owned_by($this->sharer)->create();
		$this->editor_album = Album::factory()->as_root()->owned_by($this->sharer_editor)->create();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * @param string[]           $album_ids
	 * @param array<string,bool> $grants
	 *
	 * @return array<string,mixed>
	 */
	private function addPayload(array $album_ids, array $grants = []): array
	{
		return array_merge([
			'user_ids' => [$this->userMayUpload2->id],
			'group_ids' => [],
			'album_ids' => $album_ids,
			'grants_edit' => false,
			'grants_move' => false,
			'grants_delete' => false,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
		], $grants);
	}

	/**
	 * @param array<string,bool> $grants
	 *
	 * @return array<string,mixed>
	 */
	private function editPayload(int $perm_id, array $grants = []): array
	{
		return array_merge([
			'perm_id' => $perm_id,
			'grants_edit' => false,
			'grants_move' => false,
			'grants_delete' => false,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => false,
		], $grants);
	}

	private function permissionCount(Album $album): int
	{
		return AccessPermission::query()->where('base_album_id', '=', $album->id)->count();
	}

	public function testSharerWithoutUploadOrEditCannotGiveThoseGrants(): void
	{
		foreach (['grants_upload', 'grants_edit', 'grants_move', 'grants_delete'] as $grant) {
			$before = $this->permissionCount($this->sharer_album);
			$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->addPayload([$this->sharer_album->id], [$grant => true]));
			$this->assertUnprocessable($response);
			$response->assertJsonValidationErrors([$grant]);
			self::assertSame($before, $this->permissionCount($this->sharer_album), $grant . ' must not create a permission');
		}
	}

	public function testSharerWithoutUploadOrEditGivesTheOtherGrants(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->addPayload([$this->sharer_album->id]));
		$this->assertOk($response);
	}

	public function testBulkSharingChecksGrants(): void
	{
		$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->addPayload([$this->sharer_album->id, $this->sharer_album_bis->id], ['grants_upload' => true]));
		$this->assertUnprocessable($response);
		self::assertSame(0, $this->permissionCount($this->sharer_album_bis));

		$response = $this->actingAs($this->sharer)->postJson('Sharing', $this->addPayload([$this->sharer_album->id, $this->sharer_album_bis->id]));
		$this->assertOk($response);
	}

	public function testEditingAShareChecksGrants(): void
	{
		$perm = AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($this->sharer_album)->create();

		foreach (['grants_upload', 'grants_edit', 'grants_move', 'grants_delete'] as $grant) {
			$response = $this->actingAs($this->sharer)->patchJson('Sharing', $this->editPayload($perm->id, [$grant => true]));
			$this->assertUnprocessable($response);
			$response->assertJsonValidationErrors([$grant]);
			self::assertFalse((bool) $perm->fresh()->{$grant}, $grant . ' must stay unchanged');
		}

		$response = $this->actingAs($this->sharer)->patchJson('Sharing', $this->editPayload($perm->id));
		$this->assertOk($response);
	}

	public function testSharerWithEditGivesEditGrantsButNotUpload(): void
	{
		$response = $this->actingAs($this->sharer_editor)->postJson('Sharing', $this->addPayload([$this->editor_album->id], [
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
		]));
		$this->assertOk($response);

		$response = $this->actingAs($this->sharer_editor)->postJson('Sharing', $this->addPayload([$this->editor_album->id], ['grants_upload' => true]));
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['grants_upload']);
	}

	public function testPublicUploadGrantNeedsUploadRight(): void
	{
		// The public upload grant is a Supporter Edition feature: enable it so
		// that only the held-grant rule can refuse.
		$this->requireSe();

		$uploader_payload = [
			'album_id' => $this->album1->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => true,
			'grants_full_photo_access' => false,
		];
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', $uploader_payload);
		$this->assertCreated($response);

		$payload = [
			'album_id' => $this->sharer_album->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => true,
			'grants_full_photo_access' => false,
		];
		$response = $this->actingAs($this->sharer)->postJson('Album::updateProtectionPolicy', $payload);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['grants_upload']);

		$payload['grants_upload'] = false;
		$response = $this->actingAs($this->sharer)->postJson('Album::updateProtectionPolicy', $payload);
		$this->assertCreated($response);
	}

	public function testUploaderGivesEveryGrant(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userNoUpload->id],
			'group_ids' => [],
			'album_ids' => [$this->album1->id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
	}

	public function testAdminGivesEveryGrant(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Sharing', $this->addPayload([$this->sharer_album->id], [
			'grants_upload' => true,
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
		]));
		$this->assertOk($response);
	}

	public function testPropagationCopiesExistingGrants(): void
	{
		$child = Album::factory()->children_of($this->sharer_album)->owned_by($this->sharer)->create();
		AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($this->sharer_album)->grants_upload()->grants_edit()->create();

		$response = $this->actingAs($this->sharer)->putJson('Sharing', ['album_id' => $this->sharer_album->id, 'shall_override' => false]);
		$this->assertNoContent($response);

		$copy = AccessPermission::query()->where('base_album_id', '=', $child->id)->where('user_id', '=', $this->userMayUpload2->id)->firstOrFail();
		self::assertTrue($copy->grants_upload);
		self::assertTrue($copy->grants_edit);
	}

	/**
	 * A share carrying every grant the sharer has since lost.
	 */
	private function shareWithLostGrants(): AccessPermission
	{
		return AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($this->sharer_album)
			->grants_upload()->grants_edit()->grants_move()->grants_delete()->create();
	}

	public function testResendingGrantsAlreadyGivenIsAccepted(): void
	{
		$perm = $this->shareWithLostGrants();

		$response = $this->actingAs($this->sharer)->patchJson('Sharing', $this->editPayload($perm->id, [
			'grants_download' => false,
			'grants_upload' => true,
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
		]));
		$this->assertOk($response);

		$perm = $perm->fresh();
		self::assertFalse($perm->grants_download);
		self::assertTrue($perm->grants_upload);
		self::assertTrue($perm->grants_edit);
		self::assertTrue($perm->grants_move);
		self::assertTrue($perm->grants_delete);
	}

	public function testUntickingAGrantAlreadyGivenIsAccepted(): void
	{
		$perm = $this->shareWithLostGrants();

		$response = $this->actingAs($this->sharer)->patchJson('Sharing', $this->editPayload($perm->id, [
			'grants_upload' => false,
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
		]));
		$this->assertOk($response);
		self::assertFalse($perm->fresh()->grants_upload);
		self::assertTrue($perm->fresh()->grants_edit);
	}

	public function testSwitchingOnALackedGrantNextToAGivenOneIsRefused(): void
	{
		$perm = AccessPermission::factory()->for_user($this->userMayUpload2)->for_album($this->sharer_album)->grants_upload()->create();

		$response = $this->actingAs($this->sharer)->patchJson('Sharing', $this->editPayload($perm->id, [
			'grants_upload' => true,
			'grants_edit' => true,
		]));
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['grants_edit']);
		$response->assertJsonMissingValidationErrors(['grants_upload']);
		self::assertFalse($perm->fresh()->grants_edit);
	}

	public function testNonOwnerGetsTheSameRefusalWhateverTheStoredGrant(): void
	{
		$other_sharer = User::factory()->may_share()->create();
		$given = $this->shareWithLostGrants();
		$not_given = AccessPermission::factory()->for_user($this->userNoUpload)->for_album($this->sharer_album)->create();

		foreach ([$given, $not_given] as $perm) {
			$response = $this->actingAs($other_sharer)->patchJson('Sharing', $this->editPayload($perm->id, ['grants_upload' => true]));
			$this->assertUnprocessable($response);
			$response->assertJsonValidationErrors(['grants_upload']);
		}
	}

	public function testVisibilityChangeKeepsPublicUploadGrantAlreadyGiven(): void
	{
		$this->requireSe();
		$public = AccessPermission::factory()->public()->for_album($this->sharer_album)->grants_upload()->create();

		$payload = [
			'album_id' => $this->sharer_album->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_upload' => true,
			'grants_full_photo_access' => false,
		];
		$response = $this->actingAs($this->sharer)->postJson('Album::updateProtectionPolicy', $payload);
		$this->assertCreated($response);
		self::assertTrue($this->sharer_album->fresh()->is_nsfw);
		self::assertTrue($public->fresh()->grants_upload);
	}

	public function testVisibilityNonOwnerGetsTheSameRefusalWhateverTheStoredGrant(): void
	{
		$this->requireSe();
		AccessPermission::factory()->public()->for_album($this->sharer_album)->grants_upload()->create();
		$other_sharer = User::factory()->may_share()->create();

		$response = $this->actingAs($other_sharer)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->sharer_album->id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => false,
			'grants_upload' => true,
			'grants_full_photo_access' => false,
		]);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['grants_upload']);
	}
}
