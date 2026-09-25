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
 * Feature 072 — the protection policy is reserved to the owner (Q-072-10, FR-072-40).
 */
class ProtectionPolicyOwnershipTest extends BaseApiWithDataTest
{
	use MoveGrantFixture;

	public function setUp(): void
	{
		parent::setUp();
		$this->createMoveGrantFixture();
	}

	/**
	 * @return array<string,mixed>
	 */
	private function publishWithOriginals(string $album_id): array
	{
		return [
			'album_id' => $album_id,
			'is_public' => true,
			'is_link_required' => false,
			'is_nsfw' => false,
			'grants_download' => true,
			'grants_upload' => false,
			'grants_full_photo_access' => true,
		];
	}

	private function publicPermissionExists(): bool
	{
		return AccessPermission::query()
			->where('base_album_id', '=', $this->album1->id)
			->whereNull('user_id')
			->whereNull('user_group_id')
			->exists();
	}

	/** Edit-only collaborators cannot publish the owner's album with originals and download. */
	public function testEditOnlyCollaboratorCannotChangeProtectionPolicy(): void
	{
		$this->grant($this->album1, ['edit']);

		$response = $this->actingAs($this->attacker)->postJson('Album::updateProtectionPolicy', $this->publishWithOriginals($this->album1->id));
		$this->assertForbidden($response);
		self::assertFalse($this->publicPermissionExists());
	}

	/** Even a collaborator holding every grant cannot. */
	public function testFullyGrantedCollaboratorCannotChangeProtectionPolicy(): void
	{
		$this->grant($this->album1, ['edit', 'move', 'delete', 'upload', 'download', 'full']);

		$response = $this->actingAs($this->attacker)->postJson('Album::updateProtectionPolicy', $this->publishWithOriginals($this->album1->id));
		$this->assertForbidden($response);
		self::assertFalse($this->publicPermissionExists());
	}

	public function testOwnerCanChangeProtectionPolicy(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', $this->publishWithOriginals($this->album1->id));
		$this->assertCreated($response);
		self::assertTrue($this->publicPermissionExists());
	}

	/** Smart albums have no owner: only admins may change them. */
	public function testNonAdminCannotChangeSmartAlbumProtectionPolicy(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::updateProtectionPolicy', $this->publishWithOriginals('recent'));
		$this->assertForbidden($response);
	}

	public function testAdminCanChangeProtectionPolicy(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', $this->publishWithOriginals($this->album1->id));
		$this->assertCreated($response);
	}
}
