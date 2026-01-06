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

namespace Tests\Feature_v2\RenamerRules;

use App\Enum\RenamerModeType;
use App\Models\Album;
use App\Models\Photo;
use App\Models\RenamerRule;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenameTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		\Configs::set('renamer_enabled', true);
	}

	public function tearDown(): void
	{
		\Configs::set('renamer_enabled', false);
		$this->resetSe();
		parent::tearDown();
	}

	public function testRenameUnauthorized(): void
	{
		$response = $this->patchJson('Renamer', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertUnauthorized($response);
	}

	public function testRenameForbidden(): void
	{
		// userNoUpload tries to rename a photo they don't own
		$response = $this->actingAs($this->userNoUpload)->patchJson('Renamer', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertForbidden($response);

		// userNoUpload tries to rename an album they don't own
		$response = $this->actingAs($this->userNoUpload)->patchJson('Renamer', [
			'album_ids' => [$this->album1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testRenamePhotosWithPhotoOnlyRule(): void
	{
		// Create a photo-only rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('photo_only_rule')
			->description('Photo only rule')
			->needle('CR_')
			->replacement('Photo_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		// Create test photos with titles starting with 'CR_'
		$photo1 = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('CR_1234')
			->in($this->album1)
			->create();

		$photo2 = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('CR_5678')
			->in($this->album1)
			->create();

		// Rename the photos
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo1->id, $photo2->id],
		]);
		$this->assertNoContent($response);

		// Verify the photos were renamed
		$photo1->refresh();
		$photo2->refresh();
		$this->assertSame('Photo_1234', $photo1->title);
		$this->assertSame('Photo_5678', $photo2->title);
	}

	public function testRenameAlbumsWithAlbumOnlyRule(): void
	{
		// Create an album-only rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('album_only_rule')
			->description('Album only rule')
			->needle('Album_')
			->replacement('MyAlbum_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => false,
				'is_album_rule' => true,
			])
			->create();

		// Create test albums with titles starting with 'Album_'
		$album1 = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('Album_Test1')
			->create();

		$album2 = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('Album_Test2')
			->create();

		// Rename the albums
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'album_ids' => [$album1->id, $album2->id],
		]);
		$this->assertNoContent($response);

		// Verify the albums were renamed
		$album1->refresh();
		$album2->refresh();
		$this->assertSame('MyAlbum_Test1', $album1->title);
		$this->assertSame('MyAlbum_Test2', $album2->title);
	}

	public function testRenameWithBothRule(): void
	{
		// Create a rule that applies to both photos and albums
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('both_rule')
			->description('Both rule')
			->needle('Test_')
			->replacement('Renamed_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => true,
			])
			->create();

		// Create test photo and album with titles starting with 'Test_'
		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('Test_Photo123')
			->in($this->album1)
			->create();

		$album = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('Test_Album456')
			->create();

		// Rename both photo and album
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo->id],
			'album_ids' => [$album->id],
		]);
		$this->assertNoContent($response);

		// Verify both were renamed
		$photo->refresh();
		$album->refresh();
		$this->assertSame('Renamed_Photo123', $photo->title);
		$this->assertSame('Renamed_Album456', $album->title);
	}

	public function testRenameWithAllThreeRules(): void
	{
		// Create a photo-only rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('photo_only_rule')
			->description('Photo only rule')
			->needle('TEST_')
			->replacement('Photo_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		// Create an album-only rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(2)
			->rule('album_only_rule')
			->description('Album only rule')
			->needle('TEST_')
			->replacement('Album_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => false,
				'is_album_rule' => true,
			])
			->create();

		// Create a rule that applies to both
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(3)
			->rule('both_rule')
			->description('Both rule')
			->needle('_old')
			->replacement('_new')
			->mode(RenamerModeType::ALL)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => true,
			])
			->create();

		// Create test photos and albums
		$photo1 = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('TEST_1234_old')
			->in($this->album1)
			->create();

		$photo2 = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('TEST_5678')
			->in($this->album1)
			->create();

		$album1 = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('TEST_vacation_old')
			->create();

		$album2 = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('TEST_work')
			->create();

		// Rename all photos and albums
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo1->id, $photo2->id],
			'album_ids' => [$album1->id, $album2->id],
		]);
		$this->assertNoContent($response);

		// Verify the renaming results
		$photo1->refresh();
		$photo2->refresh();
		$album1->refresh();
		$album2->refresh();

		// photo1: TEST_ -> Photo_, then _old -> _new
		$this->assertSame('Photo_1234_new', $photo1->title);
		// photo2: TEST_ -> Photo_ (no _old to replace)
		$this->assertSame('Photo_5678', $photo2->title);
		// album1: TEST_ -> Album_, then _old -> _new
		$this->assertSame('Album_vacation_new', $album1->title);
		// album2: TEST_ -> Album_ (no _old to replace)
		$this->assertSame('Album_work', $album2->title);
	}

	public function testRenameMultiplePhotosInBatch(): void
	{
		// Create a rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('batch_rule')
			->description('Batch rule')
			->needle('OLD_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		// Create 150 photos to test chunking (CHUNK_SIZE is 100)
		$photoIds = [];
		$expectations = [];
		for ($i = 1; $i <= 150; $i++) {
			$photo = Photo::factory()
				->owned_by($this->userMayUpload1)
				->with_title('OLD_' . $i)
				->in($this->album1)
				->create();
			$photoIds[] = $photo->id;
			$expectations[$photo->id] = 'NEW_' . $i;
		}

		// Rename all photos
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => $photoIds,
		]);
		$this->assertNoContent($response);

		// Verify all photos were renamed
		$photos = Photo::query()->whereIn('id', $photoIds)->orderBy('title', 'ASC')->get();
		foreach ($photos as $photo) {
			$this->assertSame($expectations[$photo->id], $photo->title);
		}
	}

	public function testRenameWithUserIsolation(): void
	{
		// Create rule for userMayUpload1
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('user1_rule')
			->description('User 1 rule')
			->needle('TEST_')
			->replacement('USER1_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => true,
			])
			->create();

		// Create rule for userMayUpload2
		RenamerRule::factory()
			->owner_id($this->userMayUpload2->id)
			->order(1)
			->rule('user2_rule')
			->description('User 2 rule')
			->needle('TEST_')
			->replacement('USER2_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => true,
			])
			->create();

		// Create photos for both users
		$photo1 = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('TEST_photo1')
			->in($this->album1)
			->create();

		$photo2 = Photo::factory()
			->owned_by($this->userMayUpload2)
			->with_title('TEST_photo2')
			->in($this->album2)
			->create();

		// User 1 renames their photo
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo1->id],
		]);
		$this->assertNoContent($response);

		// User 2 renames their photo
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Renamer', [
			'photo_ids' => [$photo2->id],
		]);
		$this->assertNoContent($response);

		// Verify each user's rules were applied correctly
		$photo1->refresh();
		$photo2->refresh();
		$this->assertSame('USER1_photo1', $photo1->title);
		$this->assertSame('USER2_photo2', $photo2->title);
	}

	public function testRenameEmptyPhotoAndAlbumIds(): void
	{
		// Create a rule
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('test_rule')
			->description('Test rule')
			->needle('TEST_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => true,
			])
			->create();

		// Call rename with empty arrays
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [],
			'album_ids' => [],
		]);
		$this->assertNoContent($response);
	}

	public function testRenameWithSharedPermissions(): void
	{
		// Create a rule for userMayUpload2
		RenamerRule::factory()
			->owner_id($this->userMayUpload2->id)
			->order(1)
			->rule('shared_rule')
			->description('Shared rule')
			->needle('SHARED_')
			->replacement('RENAMED_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		// userMayUpload2 has permission to edit album1 (via perm1)
		// Create a photo in album1
		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('SHARED_photo')
			->in($this->album1)
			->create();

		// userMayUpload2 renames the photo using their rules
		$response = $this->actingAs($this->userMayUpload2)->patchJson('Renamer', [
			'photo_ids' => [$photo->id],
		]);
		$this->assertNoContent($response);

		// Verify userMayUpload2's rules were applied
		$photo->refresh();
		$this->assertSame('RENAMED_photo', $photo->title);
	}
}
