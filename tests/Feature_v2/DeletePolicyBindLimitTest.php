<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesBulkFixtures;

/**
 * FR-112-11, S-112-16: the delete permission checks bind at most 1000
 * parameters and give the same result as before for 1200 ids.
 *
 * The checks are called through the Gate, as the delete requests do. Over
 * HTTP they are covered together with the delete itself by DeleteBindLimitTest.
 */
class DeletePolicyBindLimitTest extends BaseApiWithDataTest
{
	use CreatesBulkFixtures;

	// ── photos ──────────────────────────────────────────────────────────────

	public function testOwnerMayDelete1200Photos(): void
	{
		$photo_ids = $this->bulkPhotos($this->userMayUpload1, 1200, [$this->album1->id]);

		self::assertTrue($this->mayDeletePhotos($this->userMayUpload1, $photo_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testOneForeignPhotoInTheLastChunkIsDenied(): void
	{
		$photo_ids = $this->bulkPhotos($this->userMayUpload1, 1199, [$this->album1->id]);
		$photo_ids[] = $this->photo3->id;

		self::assertFalse($this->mayDeletePhotos($this->userMayUpload1, $photo_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testNonOwnerWithDeleteGrantOnEveryParentMayDeletePhotos(): void
	{
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1200);
		$photo_ids = $this->bulkPhotosIn($this->userMayUpload1, $album_ids);
		$this->bulkGrantDelete($album_ids, $this->userMayUpload2->id);

		self::assertTrue($this->mayDeletePhotos($this->userMayUpload2, $photo_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testNonOwnerWithoutGrantOnTheLastParentIsDeniedPhotos(): void
	{
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1200);
		$photo_ids = $this->bulkPhotosIn($this->userMayUpload1, $album_ids);
		$this->bulkGrantDelete(array_slice($album_ids, 0, 1199), $this->userMayUpload2->id);

		self::assertFalse($this->mayDeletePhotos($this->userMayUpload2, $photo_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testMemberOf600GroupsWithGrantThroughOneGroupMayDeletePhotos(): void
	{
		$member = User::factory()->may_upload()->create();
		$group_ids = $this->bulkGroupsFor($member, 600);
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1200);
		$photo_ids = $this->bulkPhotosIn($this->userMayUpload1, $album_ids);
		$this->bulkGrantDelete($album_ids, null, $group_ids[599]);

		self::assertTrue($this->mayDeletePhotos($member, $photo_ids));
		$this->assertBindingsWithinLimit();
	}

	// ── albums ──────────────────────────────────────────────────────────────

	public function testOwnerMayDelete1200Albums(): void
	{
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1200);

		self::assertTrue($this->mayDeleteAlbums($this->userMayUpload1, $album_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testOneForeignAlbumInTheLastChunkIsDenied(): void
	{
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1199);
		$album_ids[] = $this->subAlbum2->id;

		self::assertFalse($this->mayDeleteAlbums($this->userMayUpload1, $album_ids));
		$this->assertBindingsWithinLimit();
	}

	public function testNonOwnerWithDeleteGrantOnTheParentMayDeleteAlbums(): void
	{
		// perm1 grants delete on album1 to userMayUpload2.
		$album_ids = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 1200);

		self::assertTrue($this->mayDeleteAlbums($this->userMayUpload2, $album_ids));
		$this->assertBindingsWithinLimit();
	}

	/**
	 * @param string[] $photo_ids
	 */
	private function mayDeletePhotos(User $user, array $photo_ids): bool
	{
		$this->listenToBindings();

		return Gate::forUser($user)->check(PhotoPolicy::CAN_DELETE_BY_ID, [Photo::class, $photo_ids]);
	}

	/**
	 * @param string[] $album_ids
	 */
	private function mayDeleteAlbums(User $user, array $album_ids): bool
	{
		$this->listenToBindings();

		return Gate::forUser($user)->check(AlbumPolicy::CAN_DELETE_ID, [AbstractAlbum::class, $album_ids]);
	}
}
