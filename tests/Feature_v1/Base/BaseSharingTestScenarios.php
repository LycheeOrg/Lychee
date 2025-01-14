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
 * We also don't want warning about unused test methods, because these
 * methods are called by the test framework and thus only appears to be
 * seemingly unused.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection @noinspection PhpUnused
 */

namespace Tests\Feature_v1\Base;

use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Constants\TestConstants;

/**
 * Defines the "core" of common test cases which needs to be repeated
 * for different scenarios.
 *
 * The test cases defined by this class only contain the preparatory steps
 * (i.e. creating albums, uploading photos, setting sharing options, etc.)
 * but no actual assertions for expectations.
 * The assertions must be implemented by child classes with respect to the
 * scenario implemented by the child class.
 *
 * The first dimension is defined by the value of the option for public search:
 *  - enabled
 *  - disabled
 *
 * The second dimension is defined by which user interacts with Lychee:
 *  - an anonymous user
 *  - a non-admin user with whom the object under test has been shared
 *
 * This yields 2x2 = 4 scenarios, which are implemented by
 *  - {@link \Tests\Feature_v1\SharingWithAnonUserAndNoPublicSearchTest}
 *  - {@link \Tests\Feature_v1\SharingWithAnonUserAndPublicSearchTest}
 *  - {@link \Tests\Feature_v1\SharingWithNonAdminUserAndNoPublicSearchTest}
 *  - {@link \Tests\Feature_v1\SharingWithNonAdminUserAndPublicSearchTest}
 *
 * Note, the 2nd dimension could be extended by further options, e.g. a
 * non-admin user with whom the object has not been shared and a non-admin
 * user which is the owner of the object.
 * Moreover, there are further potential dimensions, e.g. options like
 * whether albums are downloadable by default, etc. pp.
 */
abstract class BaseSharingTestScenarios extends BaseSharingTest
{
	/** @var string|null the ID of the current album 1 (if applicable by the test) */
	protected ?string $albumID1;

	/** @var string|null the ID of the current album 2 (if applicable by the test) */
	protected ?string $albumID2;

	/** @var string|null the ID of the current album 3 (if applicable by the test) */
	protected ?string $albumID3;

	/** @var string|null the ID of the current photo 1 (if applicable by the test) */
	protected ?string $photoID1;

	/** @var string|null the ID of the current photo 2 (if applicable by the test) */
	protected ?string $photoID2;

	/** @var string|null the ID of the current photo 3 (if applicable by the test) */
	protected ?string $photoID3;

	/** @var int|null the ID of the current user (if applicable by the test) */
	protected ?int $userID;

	public function setUp(): void
	{
		parent::setUp();
		// Reset all variables to ensure that a test implementation of the
		// child class does not accidentally use a value which it should not.
		$this->albumID1 = null;
		$this->albumID2 = null;
		$this->albumID3 = null;
		$this->photoID1 = null;
		$this->photoID2 = null;
		$this->photoID3 = null;
		$this->userID = $this->users_tests->add(TestConstants::USER_NAME_1, TestConstants::USER_PWD_1)->offsetGet('id');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	abstract protected function generateExpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		?string $onThisDayAlbumThumbID = null,
		array $expectedAlbumJson = [],
	): array;

	abstract protected function generateUnexpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
	): ?array;

	abstract protected function generateExpectedTreeJson(array $expectedAlbums = []): array;

	/**
	 * Uploads an unsorted, private photo and logs out.
	 *
	 * @return void
	 */
	protected function prepareUnsortedPrivatePhoto(): void
	{
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	/**
	 * Ensures that the user does not see the private photo.
	 *
	 * See {@link BaseSharingTestScenarios::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		$this->prepareUnsortedPrivatePhoto();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$arrayUnexpected = $this->generateUnexpectedRootJson();
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);

		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
	}

	/**
	 * Uploads two unsorted photos, marks the first photo as public, stars it
	 * and logs out.
	 *
	 * @return void
	 */
	protected function prepareUnsortedPublicAndPrivatePhoto(): void
	{
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		$this->photos_tests->set_star([$this->photoID1], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testUnsortedPublicAndPrivatePhoto(): void;

	/**
	 * Creates an album with two photos, marks the first photo as public stars
	 * it and logs out.
	 *
	 * This scenario is similar to
	 * {@link BaseSharingTestScenarios::prepareUnsortedPublicAndPrivatePhoto()}
	 * but with an album.
	 *
	 * @return void
	 */
	protected function preparePublicAndPrivatePhotoInPrivateAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photos_tests->set_star([$this->photoID1], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testPublicAndPrivatePhotoInPrivateAlbum(): void;

	/**
	 * Creates an album with three photos, marks the album as public, stars
	 * the alphabetically last photo and logs out.
	 *
	 * @return void
	 */
	protected function prepareThreePhotosInPublicAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID3 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $this->albumID1)->offsetGet('id');
		$this->albums_tests->set_protection_policy($this->albumID1);
		$this->photos_tests->set_star([$this->photoID1], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	/**
	 * See
	 * {@link SharingWithAnonUserAndNoPublicSearchTest::testUnsortedPublicAndPrivatePhoto()}
	 * and
	 * {@link SharingWithAnonUserAndNoPublicSearchTest::testPublicAndPrivatePhotoInPrivateAlbum()}.
	 *
	 * In comparison both photos are visible, because the album is public,
	 * although public search is disabled, because
	 * the photo is inside a public album which is browsable.
	 *
	 * Note that the setting "public search" only affects photos which are
	 * made public explicitly.
	 */
	public function testThreePhotosInPublicAlbum(): void
	{
		$this->prepareThreePhotosInPublicAlbum();

		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID1);
		$this->ensurePhotosWereTakenOnThisDay($this->photoID2);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID3);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1,
			null,
			$this->photoID1,
			$this->photoID2, [
				// photo 1 is thumb, because starred photo are always picked first
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			])
		);
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, $this->photoID1, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID3]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				// photo 1 is the thumb, because starred photo are always picked first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, $this->photoID3, $this->albumID1), // photo 3 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is next alphabetically
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID3]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID3]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
		]));
		$responseForTree->assertJsonMissing(['id' => $this->photoID3]);

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, $this->photoID3, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			],
		]);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2);
		$this->photos_tests->get($this->photoID3);
	}

	/**
	 * Uploads an unsorted photo and another photo into an album, marks the
	 * unsorted photo as public and stars it, shares the album with a
	 * non-admin user, stars the photo inside the shared album and logs out.
	 *
	 * @return void
	 */
	protected function preparePublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->sharing_tests->add([$this->albumID1], [$this->userID]);
		$this->photos_tests->set_star([$this->photoID1, $this->photoID2], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void;

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as password-protected and
	 * logs out.
	 *
	 * @return void
	 */
	protected function preparePublicAlbumAndPasswordProtectedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->albums_tests->set_protection_policy(id: $this->albumID1, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy(id: $this->albumID2);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	public function testPublicAlbumAndPasswordProtectedAlbum(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID2,
			$this->photoID2, [
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID2);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
	}

	public function testPublicAlbumAndPasswordProtectedUnlockedAlbum(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID2);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID1);

		$this->albums_tests->unlock($this->albumID1, TestConstants::ALBUM_PWD_1);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID2, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [ // photo 2 was taken on this day
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			self::generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
		]));
	}

	/**
	 * Like {@link BaseSharingTestScenarios::preparePublicAlbumAndPasswordProtectedAlbum},
	 * but additionally the password-protected photo is starred.
	 *
	 * @return void
	 */
	protected function preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->albums_tests->set_protection_policy(id: $this->albumID1, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy($this->albumID2);
		$this->photos_tests->set_star([$this->photoID1], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	public function testPublicAlbumAndPasswordProtectedAlbumWithStarredPhoto(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID2,
			$this->photoID2, [  // album 1 is password protected, hence photo 2 is the thumb
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID2);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
	}

	public function testPublicAlbumAndPasswordProtectedUnlockedAlbumWithStarredPhoto(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto();
		$this->albums_tests->unlock($this->albumID1, TestConstants::ALBUM_PWD_1);

		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1,  // album 1 is unlocked, and photo 1 is alphabetically first
			null,
			$this->photoID1,
			null, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, $this->photoID1, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID1]);
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			self::generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, null, $this->photoID2),
		]));
	}

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as public as well as hidden,
	 * stars the photo in the hidden album and logs out.
	 *
	 * @return void
	 */
	protected function preparePublicAlbumAndHiddenAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->albums_tests->set_protection_policy(id: $this->albumID1);
		$this->albums_tests->set_protection_policy(id: $this->albumID2, is_link_required: true);
		$this->photos_tests->set_star([$this->photoID2], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	public function testPublicAlbumAndHiddenAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	/**
	 * Like {@link BaseSharingTestScenarios::preparePublicAlbumAndHiddenAlbum}, but
	 * additionally the hidden album is also password protected.
	 *
	 * @return void
	 */
	protected function preparePublicAlbumAndHiddenPasswordProtectedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->albums_tests->set_protection_policy(id: $this->albumID1);
		$this->albums_tests->set_protection_policy(id: $this->albumID2, is_link_required: true, password: TestConstants::ALBUM_PWD_2);
		$this->photos_tests->set_star([$this->photoID2], true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	public function testPublicAlbumAndHiddenPasswordProtectedAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenPasswordProtectedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage());

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	public function testPublicAlbumAndHiddenPasswordProtectedUnlockedAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenPasswordProtectedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$this->albums_tests->unlock($this->albumID2, TestConstants::ALBUM_PWD_2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => TestConstants::ALBUM_TITLE_2,
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	/**
	 * Creates three albums, puts a single photo in each, shares two
	 * album with a user, mark one as public with requireLink and logs out.
	 *
	 * @return void
	 */
	protected function preparePhotosInSharedAndPrivateAndRequireLinkAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->albumID3 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_3)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->photoID3 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $this->albumID3)->offsetGet('id');
		$this->sharing_tests->add([$this->albumID1, $this->albumID3], [$this->userID]);
		$this->albums_tests->set_protection_policy(id: $this->albumID3, is_link_required: true);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testPhotosInSharedAndPrivateAlbum(): void;

	/**
	 * Creates an album, uploads a photo, shares the album with a user, marks
	 * it as public as well as password protected and logs out.
	 *
	 * This scenario asserts that sharing an album with a user takes
	 * precedence over password protection.
	 *
	 * @return void
	 */
	protected function preparePhotoInSharedPublicPasswordProtectedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->albums_tests->set_protection_policy(id: $this->albumID1, password: TestConstants::ALBUM_PWD_1);
		$this->sharing_tests->add([$this->albumID1], [$this->userID]);
		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testPhotoInSharedPublicPasswordProtectedAlbum(): void;

	public function testPhotoInSharedPublicPasswordProtectedUnlockedAlbum(): void
	{
		$this->preparePhotoInSharedPublicPasswordProtectedAlbum();
		$this->albums_tests->unlock($this->albumID1, TestConstants::ALBUM_PWD_1);

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1,
			$this->photoID1, [
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(null, null, null, $this->photoID1);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1),
		]));

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson($this->generateExpectedAlbumJson(
			$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);
	}

	/**
	 * Create three albums with on photo each, share the first and second with
	 * a user, mark the second and third as public and password protected
	 * with the same password and logs out.
	 *
	 * This scenario asserts that unlocking an album does not badly interfere
	 * with another album which is shared but also happens to be protected
	 * by the same password.
	 *
	 * @return void
	 */
	protected function prepareThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->albumID3 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_3)->offsetGet('id');
		// The mis-order of photos by there title (N, T, M) is on purpose such that
		// we first receive the result (N, T) as long as album 3 is locked and
		// then (M, N, T) after album 3 has been unlocked, with album 3
		// being in the front position.
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->photoID3 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID3)->offsetGet('id');
		$this->sharing_tests->add([$this->albumID1, $this->albumID2], [$this->userID]);
		// Sic! We use the same password for both albums here, because we want
		// to ensure that incidentally "unlocking" an album which is also
		// shared has no negative side effect.
		$this->albums_tests->set_protection_policy(id: $this->albumID2, is_nsfw: false, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy(id: $this->albumID3, is_nsfw: false, password: TestConstants::ALBUM_PWD_1);

		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
		$this->performPostPreparatorySteps();
	}

	abstract public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void;

	abstract public function testThreeUnlockedAlbumsWithMixedSharingAndPasswordProtection(): void;

	abstract protected function performPostPreparatorySteps(): void;

	abstract protected function getExpectedInaccessibleHttpStatusCode(): int;

	abstract protected function getExpectedDefaultInaccessibleMessage(): string;
}
