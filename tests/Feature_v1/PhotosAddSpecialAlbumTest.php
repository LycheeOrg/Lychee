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

namespace Tests\Feature_v1;

use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;
use Tests\Traits\InteractWithSmartAlbums;

/**
 * Contains tests which add photos to Lychee and directly set an album.
 */
class PhotosAddSpecialAlbumTest extends BasePhotoTest
{
	use InteractWithSmartAlbums;

	/**
	 * A simple upload of an ordinary photo to a regular album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToSubAlbum(): void
	{
		$album_id = null;

		try {
			$album_id = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');

			$response = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
				$album_id
			);
			$response->assertJson(['album_id' => $album_id]);
		} finally {
			if ($album_id !== null) {
				$this->albums_tests->delete([$album_id]);
			}
		}
	}

	/**
	 * A simple upload of an ordinary photo to the is-starred album.
	 *
	 * @return void
	 */
	public function testSimpleUploadToIsStarred(): void
	{
		$response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			StarredAlbum::ID
		);
		$response->assertJson([
			'album_id' => null,
			'is_starred' => true,
		]);
	}

	public function testRecentAlbum(): void
	{
		$ids_before = static::getRecentPhotoIDs();

		$this->clearCachedSmartAlbums();
		/** @var \App\SmartAlbums\BaseSmartAlbum $recentAlbumBefore */
		$recentAlbumBefore = static::convertJsonToObject($this->albums_tests->get(RecentAlbum::ID));
		static::assertCount($ids_before->count(), $recentAlbumBefore->photos);

		$photo_id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$ids_after = static::getRecentPhotoIDs();

		$this->clearCachedSmartAlbums();
		/** @var \App\SmartAlbums\BaseSmartAlbum $recentAlbumAfter */
		$recentAlbumAfter = static::convertJsonToObject($this->albums_tests->get(RecentAlbum::ID));
		static::assertCount($ids_after->count(), $recentAlbumAfter->photos);

		$new_ids = $ids_after->diff($ids_before);
		static::assertCount(1, $new_ids);
		static::assertEquals($photo_id, $new_ids->first());

		$this->photos_tests->delete([$photo_id]);

		$this->clearCachedSmartAlbums();
		/** @var \App\SmartAlbums\BaseSmartAlbum $recentAlbum */
		$recentAlbum = static::convertJsonToObject($this->albums_tests->get(RecentAlbum::ID));
		static::assertEquals($recentAlbumBefore->photos, $recentAlbum->photos);
	}
}
