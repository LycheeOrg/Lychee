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

use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Support\Facades\Auth;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BaseSharingTestScenarios;

/**
 * Implements the tests of {@link BaseSharingTestScenarios} for a
 * non-admin user whose results are independent of the setting for public
 * search.
 */
class SharingWithNonAdminUserTest extends BaseSharingTestScenarios
{
	public function setUp(): void
	{
		parent::setUp();
	}

	/**
	 * {@inheritDoc}
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		parent::testUnsortedPrivatePhoto();

		$response_for_unsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$response_for_unsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID1]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function testThreePhotosInPublicAlbum(): void
	{
		parent::testThreePhotosInPublicAlbum();

		$response_for_unsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$response_for_unsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID2]);
	}

	public function testPhotosInSharedAndPrivateAlbum(): void
	{
		$this->preparePhotosInSharedAndPrivateAndRequireLinkAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID3);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID3,
			$this->photoID1, [
				self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
				self::generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
			]
		));
		$response_for_root->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID3, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			self::generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
		]));
		$response_for_tree->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_album1 = $this->albums_tests->get($this->albumID1);
		$response_for_album1->assertJson(self::generateExpectedAlbumJson(
			$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);

		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());

		$response_for_album3 = $this->albums_tests->get($this->albumID3);
		$response_for_album3->assertJson(self::generateExpectedAlbumJson(
			$this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3
		));
		$this->photos_tests->get($this->photoID3);
	}

	public function testPhotoInSharedPublicPasswordProtectedAlbum(): void
	{
		$this->preparePhotoInSharedPublicPasswordProtectedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID1, [
				self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
		]));

		$response_for_album = $this->albums_tests->get($this->albumID1);
		$response_for_album->assertJson($this->generateExpectedAlbumJson(
			$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);
	}

	public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID2, $this->photoID3);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID1);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,  // photo 1 is alphabetically first, as photo 3 is locked
			$this->photoID2,  // photo 1 was not taken on this day and photo 3 is locked
			[
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
				$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3), // album 3 is locked, hence no thumb
			]
		));
		$response_for_root->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_starred->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // photo 1 is alphabetically first, as photo 3 is locked
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		// TODO: Should public and password-protected albums appear in tree? Regression?
		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
		]));
		$response_for_tree->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_album1 = $this->albums_tests->get($this->albumID1);
		$response_for_album1->assertJson($this->generateExpectedAlbumJson(
			$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);
		$response_for_album2 = $this->albums_tests->get($this->albumID2);
		$response_for_album2->assertJson($this->generateExpectedAlbumJson(
			$this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2
		));
		$this->photos_tests->get($this->photoID2);
		$this->albums_tests->get($this->albumID3, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID3, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testThreeUnlockedAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();
		$this->albums_tests->unlock($this->albumID3, TestConstants::ALBUM_PWD_1);

		$this->ensurePhotosWereTakenOnThisDay($this->photoID2);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID1, $this->photoID3);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID3, // photo 3 is alphabetically first
			$this->photoID2, // photo 2 was taken on this day
			[
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
				$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
			]
		));

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_starred->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID3, [ // photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
		]));

		$response_for_album1 = $this->albums_tests->get($this->albumID1);
		$response_for_album1->assertJson($this->generateExpectedAlbumJson(
			$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);
		$response_for_album2 = $this->albums_tests->get($this->albumID2);
		$response_for_album2->assertJson($this->generateExpectedAlbumJson(
			$this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2
		));
		$this->photos_tests->get($this->photoID2);
		$response_for_album3 = $this->albums_tests->get($this->albumID3);
		$response_for_album3->assertJson($this->generateExpectedAlbumJson(
			$this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3
		));
		$this->photos_tests->get($this->photoID3);
	}

	protected function generateExpectedRootJson(
		?string $unsorted_album_thumb_i_d = null,
		?string $starred_album_thumb_i_d = null,
		?string $public_album_thumb_i_d = null,
		?string $recent_album_thumb_i_d = null,
		?string $on_this_day_album_thumb_i_d = null,
		array $expected_album_json = [],
	): array {
		return [
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($unsorted_album_thumb_i_d)],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starred_album_thumb_i_d)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recent_album_thumb_i_d)],
				OnThisDayAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($on_this_day_album_thumb_i_d)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => $expected_album_json,
		];
	}

	protected function generateUnexpectedRootJson(
		?string $unsorted_album_thumb_i_d = null,
		?string $starred_album_thumb_i_d = null,
		?string $public_album_thumb_i_d = null,
		?string $recent_album_thumb_i_d = null,
		array $expected_album_json = [],
	): ?array {
		return null;
	}

	protected function generateExpectedTreeJson(array $expected_albums = []): array
	{
		return [
			'albums' => [],
			'shared_albums' => $expected_albums,
		];
	}

	protected function performPostPreparatorySteps(): void
	{
		Auth::loginUsingId($this->userID);
	}

	protected function getExpectedInaccessibleHttpStatusCode(): int
	{
		return 403;
	}

	protected function getExpectedDefaultInaccessibleMessage(): string
	{
		return TestConstants::EXPECTED_FORBIDDEN_MSG;
	}

	/**
	 * Ensures that the user does not the unsorted public photos as covers nor
	 * inside "Recent", "On This Day" and "Favorites" (as public search is disabled).
	 * The user can access the public photo nonetheless, but gets
	 * "403 - Forbidden" for the other.
	 *
	 * See {@link BaseSharingTestScenarios::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPublicAndPrivatePhoto(): void
	{
		$this->prepareUnsortedPublicAndPrivatePhoto();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson());
		$array_unexpected = $this->generateUnexpectedRootJson();
		if ($array_unexpected !== null) {
			$response_for_root->assertJsonMissing($array_unexpected);
		}
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_unsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$response_for_unsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID2]);

		// Even though the public photo is not searchable and hence does not
		// show up in the smart albums, it can be fetched directly
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	/**
	 * Ensures that the user does not see any photo, although the first is
	 * public (but not searchable).
	 * The first photo is still visible if directly accessed, but the user
	 * gets a `403 - Forbidden` for the album and the second photo.
	 *
	 * See
	 * {@link BaseSharingTestScenarios::preparePublicAndPrivatePhotoInPrivateAlbum()}
	 * for description of scenario.
	 */
	public function testPublicAndPrivatePhotoInPrivateAlbum(): void
	{
		$this->preparePublicAndPrivatePhotoInPrivateAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson());
		$array_unexpected = $this->generateUnexpectedRootJson();
		if ($array_unexpected !== null) {
			$response_for_root->assertJsonMissing($array_unexpected);
		}
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_unsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$response_for_unsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson());
		$response_for_tree->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		// Even though public search is disabled, the photo is accessible
		// by its direct link, because it is public.
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->preparePublicUnsortedPhotoAndPhotoInSharedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID2,
			null,
			$this->photoID2,
			$this->photoID2, [
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID2),
			]
		));
		$array_unexpected = $this->generateUnexpectedRootJson(
			null, $this->photoID1, null, $this->photoID2
		);
		if ($array_unexpected !== null) {
			$response_for_root->assertJsonMissing($array_unexpected);
		}
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_unsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$response_for_unsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_unsorted->assertJsonMissing(['id' => $this->photoID2]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			]
		));
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			]
		));
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			]
		));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID2),
		]));

		$response_for_album = $this->albums_tests->get($this->albumID1);
		$response_for_album->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => false],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			],
		]);

		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2);
	}
}
