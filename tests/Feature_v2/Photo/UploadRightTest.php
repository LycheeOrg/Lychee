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

namespace Tests\Feature_v2\Photo;

use App\Contracts\Models\AbstractAlbum;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: owners need `may_upload` to upload into their own albums;
 * an upload grant still works for its recipient (FR-118-13, S-118-08).
 */
class UploadRightTest extends BaseApiWithDataTest
{
	private User $owner_without_upload;
	private Album $own_album;

	public function setUp(): void
	{
		parent::setUp();
		$this->owner_without_upload = User::factory()->may_share()->may_edit()->create();
		$this->own_album = Album::factory()->as_root()->owned_by($this->owner_without_upload)->create();
	}

	public function testOwnerWithoutUploadCannotUploadIntoOwnAlbum(): void
	{
		$response = $this->actingAs($this->owner_without_upload)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->own_album->id);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->owner_without_upload)->postJson('Photo::fromUrl', [
			'album_id' => $this->own_album->id,
			'urls' => ['https://example.com'],
		]);
		$this->assertForbidden($response);

		$this->actingAs($this->owner_without_upload);
		self::assertFalse(Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->own_album]));
	}

	public function testUploadGrantStillWorksWithoutUploadRight(): void
	{
		AccessPermission::factory()->for_user($this->owner_without_upload)->for_album($this->album1)->visible()->grants_upload()->create();

		$this->actingAs($this->owner_without_upload->fresh());
		self::assertTrue(Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album1->fresh()]));
	}

	public function testOwnerWithUploadUploadsIntoOwnAlbum(): void
	{
		$this->actingAs($this->userMayUpload1);
		self::assertTrue(Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album1]));
	}
}
