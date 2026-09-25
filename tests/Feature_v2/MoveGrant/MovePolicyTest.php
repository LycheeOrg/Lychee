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

use App\Contracts\Models\AbstractAlbum;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * `AlbumPolicy::CAN_MOVE` (content), `AlbumPolicy::CAN_MOVE_ALBUM`
 * (the album itself, grant on its parent) and `PhotoPolicy::CAN_MOVE`.
 */
class MovePolicyTest extends BaseApiWithDataTest
{
	private function canMoveAlbum(User $user, Album $album): bool
	{
		$this->actingAs($user);

		return Gate::check(AlbumPolicy::CAN_MOVE, [AbstractAlbum::class, Album::query()->findOrFail($album->id)]);
	}

	private function canMoveAlbumItself(User $user, Album $album): bool
	{
		$this->actingAs($user);

		return Gate::check(AlbumPolicy::CAN_MOVE_ALBUM, [AbstractAlbum::class, Album::query()->findOrFail($album->id)]);
	}

	private function canMovePhoto(User $user, Photo $photo): bool
	{
		$this->actingAs($user);

		return Gate::check(PhotoPolicy::CAN_MOVE, [Photo::class, Photo::query()->findOrFail($photo->id)]);
	}

	private function editOnlyUserOn(Album $album): User
	{
		$user = User::factory()->may_upload()->create();
		AccessPermission::factory()->for_user($user)->for_album($album)->visible()->grants_edit()->create();

		return $user;
	}

	public function testOwnerWithUploadCanMove(): void
	{
		self::assertTrue($this->canMoveAlbum($this->userMayUpload1, $this->album1));
	}

	public function testOwnerWithoutUploadCannotMove(): void
	{
		self::assertFalse($this->canMoveAlbum($this->userNoUpload, $this->album3));
	}

	public function testUserGrantAllowsMove(): void
	{
		self::assertTrue($this->canMoveAlbum($this->userMayUpload2, $this->album1));
	}

	public function testGroupGrantAllowsMove(): void
	{
		self::assertTrue($this->canMoveAlbum($this->userWithGroup1, $this->album1));
	}

	public function testEditOnlyGrantDoesNotAllowMove(): void
	{
		$user = $this->editOnlyUserOn($this->album2);
		self::assertFalse($this->canMoveAlbum($user, $this->album2));
	}

	public function testPublicGrantNeverAllowsMove(): void
	{
		// A public permission never confers move, even if the column were set.
		AccessPermission::query()->where('id', '=', $this->perm4->id)->update(['grants_move' => true]);
		self::assertFalse($this->canMoveAlbum($this->userMayUpload2, $this->album4));
	}

	public function testAdminCanMove(): void
	{
		self::assertTrue($this->canMoveAlbum($this->admin, $this->album2));
	}

	public function testPhotoOwnerCanMove(): void
	{
		self::assertTrue($this->canMovePhoto($this->userMayUpload2, $this->photo2));
	}

	public function testPhotoMoveViaContainingAlbumGrant(): void
	{
		self::assertTrue($this->canMovePhoto($this->userMayUpload2, $this->photo1));
	}

	public function testPhotoEditOnlyGrantDoesNotAllowMove(): void
	{
		$user = $this->editOnlyUserOn($this->album2);
		self::assertFalse($this->canMovePhoto($user, $this->photo2));
	}

	public function testUnsortedPhotoOfOtherUserCannotBeMoved(): void
	{
		self::assertFalse($this->canMovePhoto($this->userMayUpload2, $this->photoUnsorted));
	}

	public function testOwnerCanMoveOwnAlbumItself(): void
	{
		self::assertTrue($this->canMoveAlbumItself($this->userMayUpload1, $this->album1));
		self::assertTrue($this->canMoveAlbumItself($this->userMayUpload1, $this->subAlbum1));
	}

	public function testMoveGrantOnParentAllowsMovingChild(): void
	{
		// perm1 grants move on album1 to userMayUpload2.
		self::assertTrue($this->canMoveAlbumItself($this->userMayUpload2, $this->subAlbum1));
		self::assertTrue($this->canMoveAlbumItself($this->userWithGroup1, $this->subAlbum1));
	}

	public function testMoveGrantOnAlbumDoesNotAllowMovingIt(): void
	{
		// perm1 is on album1 itself; album1 is a root album of another user.
		self::assertFalse($this->canMoveAlbumItself($this->userMayUpload2, $this->album1));
	}

	public function testEditOnParentDoesNotAllowMovingChild(): void
	{
		$user = $this->editOnlyUserOn($this->album2);
		self::assertFalse($this->canMoveAlbumItself($user, $this->subAlbum2));
	}
}
