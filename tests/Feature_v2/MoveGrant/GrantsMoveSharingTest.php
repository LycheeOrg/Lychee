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

namespace Tests\Feature_v2\MoveGrant;

use App\Models\AccessPermission;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * `grants_move` on the sharing API.
 */
class GrantsMoveSharingTest extends BaseApiWithDataTest
{
	/**
	 * @return array<string,mixed>
	 */
	private function createPayload(array $extra = []): array
	{
		return array_merge([
			'user_ids' => [$this->userMayUpload1->id],
			'group_ids' => [],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_delete' => false,
			'grants_download' => false,
			'grants_full_photo_access' => false,
			'grants_upload' => false,
		], $extra);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function editPayload(array $extra = []): array
	{
		return array_merge([
			'perm_id' => $this->perm1->id,
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		], $extra);
	}

	private function storedMove(int $perm_id): bool
	{
		return AccessPermission::query()->findOrFail($perm_id)->grants_move;
	}

	public function testCreateWithoutGrantsMoveIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', $this->createPayload());
		$this->assertUnprocessable($response);
	}

	public function testCreateWithGrantsMoveStoresTrue(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', $this->createPayload(['grants_move' => true]));
		$this->assertOk($response);
		$response->assertJsonPath('0.grants_move', true);
		self::assertTrue($this->storedMove($response->json('0.id')));
	}

	public function testCreateWithNonBooleanGrantsMoveIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', $this->createPayload(['grants_move' => 'nope']));
		$this->assertUnprocessable($response);
	}

	public function testEditWithoutGrantsMoveIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', $this->editPayload());
		$this->assertUnprocessable($response);
		self::assertTrue($this->storedMove($this->perm1->id));
	}

	public function testEditWithNonBooleanGrantsMoveIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', $this->editPayload(['grants_move' => 'nope']));
		$this->assertUnprocessable($response);
	}

	public function testEditWithGrantsMoveUpdatesIt(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', $this->editPayload(['grants_move' => false]));
		$this->assertOk($response);
		$response->assertJsonPath('grants_move', false);
		self::assertFalse($this->storedMove($this->perm1->id));
	}

	public function testPropagateUpdateCopiesGrantsMove(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => false,
		]);
		$this->assertNoContent($response);

		$perm = AccessPermission::query()
			->where('base_album_id', '=', $this->subAlbum1->id)
			->where('user_id', '=', $this->userMayUpload2->id)
			->firstOrFail();
		self::assertTrue($perm->grants_move);
	}

	public function testPropagateOverrideCopiesGrantsMove(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Sharing', [
			'album_id' => $this->album1->id,
			'shall_override' => true,
		]);
		$this->assertNoContent($response);

		$perm = AccessPermission::query()
			->where('base_album_id', '=', $this->subAlbum1->id)
			->where('user_id', '=', $this->userMayUpload2->id)
			->firstOrFail();
		self::assertTrue($perm->grants_move);
	}
}
