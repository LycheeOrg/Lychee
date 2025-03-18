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
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BaseSharingTestScenarios;

/**
 * Implements the tests of {@link BaseSharingTestScenarios} for an
 * anonymous user whose results are independent of the setting for public
 * search.
 */
class SharingWithAnonUserTest extends BaseSharingTestScenarios
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testPhotosInSharedAndPrivateAlbum(): void
	{
		$this->preparePhotosInSharedAndPrivateAndRequireLinkAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2, $this->photoID3);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson());
		$response_for_root->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_root->assertJsonMissing(['id' => $this->albumID3]);
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
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_recent->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson());
		$response_for_tree->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_tree->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID3]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
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
			null,
			null, [
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1),
			]
		));
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_starred = $this->albums_tests->get(StarredAlbum::ID);
		$response_for_starred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_starred->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_starred->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_recent = $this->albums_tests->get(RecentAlbum::ID);
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson());
		$response_for_tree->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2, $this->photoID3);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			null,
			null, [
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2),
				$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3),
			]
		));
		$response_for_root->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);
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
		$response_for_recent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_recent->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(true));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		// TODO: Should public and password-protected albums appear in tree? Regression?
		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson());
		$response_for_tree->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->albumID2]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID2]);
		$response_for_tree->assertJsonMissing(['id' => $this->albumID3]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID3]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID3, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID3, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testThreeUnlockedAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();
		$this->albums_tests->unlock($this->albumID3, TestConstants::ALBUM_PWD_1);

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID3);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID3,
			$this->photoID2,
			[ // photo 3 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
				$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
			],
		));

		$response_for_root->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);

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
			$this->photoID3, [ // photo 3 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$response_for_recent->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_recent->assertJsonMissing(['id' => $this->photoID1]);

		$response_for_on_this_day = $this->albums_tests->get(OnThisDayAlbum::ID);
		$response_for_on_this_day->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [ // photo 2 was taken on this day
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_on_this_day->assertJsonMissing(['id' => $this->photoID3]);

		$response_for_tree = $this->root_album_tests->getTree();
		$response_for_tree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, null, $this->photoID3),
		]));
		$response_for_tree->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_tree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
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
		if ($unsorted_album_thumb_i_d !== null) {
			throw new \InvalidArgumentException('$unsortedAlbumThumbID must be `null` for test with unauthenticated users');
		}
		if ($public_album_thumb_i_d !== null) {
			throw new \InvalidArgumentException('$publicAlbumThumbID must be `null` for test with unauthenticated users');
		}

		return [
			'smart_albums' => [
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starred_album_thumb_i_d)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recent_album_thumb_i_d)],
				OnThisDayAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($on_this_day_album_thumb_i_d)],
			],
			'tag_albums' => [],
			'albums' => $expected_album_json,
			'shared_albums' => [],
		];
	}

	protected function generateUnexpectedRootJson(
		?string $unsorted_album_thumb_i_d = null,
		?string $starred_album_thumb_i_d = null,
		?string $public_album_thumb_i_d = null,
		?string $recent_album_thumb_i_d = null,
		array $expected_album_json = [],
	): array {
		if ($unsorted_album_thumb_i_d !== null) {
			throw new \InvalidArgumentException('$unsortedAlbumThumbID must be `null` for test with unauthenticated users');
		}
		if ($public_album_thumb_i_d !== null) {
			throw new \InvalidArgumentException('$publicAlbumThumbID must be `null` for test with unauthenticated users');
		}

		$smart_albums = [
			UnsortedAlbum::ID => null,
		];
		if ($starred_album_thumb_i_d === null) {
			$smart_albums[StarredAlbum::ID] = null;
		}
		if ($recent_album_thumb_i_d === null) {
			$smart_albums[RecentAlbum::ID] = null;
		}

		return [
			'smart_albums' => $smart_albums,
		];
	}

	protected function generateExpectedTreeJson(array $expected_albums = []): array
	{
		return [
			'albums' => $expected_albums,
			'shared_albums' => [],
		];
	}

	protected function performPostPreparatorySteps(): void
	{
		// This is a no-op for the anonymous user, because we do not need
		// to log in
	}

	protected function getExpectedInaccessibleHttpStatusCode(): int
	{
		return 401;
	}

	protected function getExpectedDefaultInaccessibleMessage(): string
	{
		return TestConstants::EXPECTED_UNAUTHENTICATED_MSG;
	}

	protected function getExpectedForbiddenHttpStatusCode(): int
	{
		return 403;
	}

	/**
	 * Ensures that the user does not see the unsorted public photos as covers nor
	 * inside "Recent", "On This Day" and "Favorites" (as public search is disabled).
	 * The user can access the public photo nonetheless, but gets
	 * "401 - Unauthenticated" for the other.
	 *
	 * See
	 * {@link BaseSharingTestScenarios::prepareUnsortedPublicAndPrivatePhoto()}
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
		$response_for_root->assertJsonMissing($this->generateUnexpectedRootJson());
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

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

		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	/**
	 * Ensures that the user does not see any photo, although the first is
	 * public (but not searchable).
	 * The first photo is still visible if directly accessed, but the user
	 * gets a `401 - Unauthenticated` for the album and the second photo.
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
		$response_for_root->assertJsonMissing($this->generateUnexpectedRootJson());
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

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
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->preparePublicUnsortedPhotoAndPhotoInSharedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$response_for_root = $this->root_album_tests->get();
		$response_for_root->assertJson($this->generateExpectedRootJson());
		$response_for_root->assertJsonMissing($this->generateUnexpectedRootJson());
		$response_for_root->assertJsonMissing(['id' => $this->albumID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID1]);
		$response_for_root->assertJsonMissing(['id' => $this->photoID2]);

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
		$response_for_tree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}
}
