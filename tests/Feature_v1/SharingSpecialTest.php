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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BaseSharingTest;

class SharingSpecialTest extends BaseSharingTest
{
	protected ?string $albumID1 = null;
	protected ?string $albumID2 = null;
	protected ?string $albumID3 = null;
	protected ?string $albumID4 = null;
	protected ?string $albumID5 = null;
	protected ?string $albumID6 = null;
	protected ?string $photoID1 = null;
	protected ?string $photoID2 = null;
	protected ?string $photoID3 = null;
	protected ?string $photoID4 = null;
	protected ?string $photoID5 = null;
	protected ?string $photoID6 = null;

	public function setUp(): void
	{
		parent::setUp();

		// Clear temporary variables between tests
		$this->albumID1 = null;
		$this->albumID2 = null;
		$this->albumID3 = null;
		$this->albumID4 = null;
		$this->albumID5 = null;
		$this->albumID6 = null;
		$this->photoID1 = null;
		$this->photoID2 = null;
		$this->photoID3 = null;
		$this->photoID4 = null;
		$this->photoID5 = null;
		$this->photoID6 = null;
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	 * Preparse a scenario with six albums and one photo each as well as
	 * varying protection settings.
	 *
	 * This is the scenario for
	 * [Bug #1155](https://github.com/LycheeOrg/Lychee/issues/1155).
	 *
	 * ```
	 *  A       (public, password-protected "foo")
	 *  |
	 *  +-- B   (public)
	 *  |
	 *  +-- C   (public, password-protected "foo")
	 *  |
	 *  +-- D   (public, password-protected "foo", hidden)
	 *  |
	 *  +-- E   (public, password-protected "bar")
	 *  |
	 *  +-- F   (public, password-protected "bar", hidden)
	 * ```
	 *
	 * @return void
	 */
	protected function prepareSixAlbumsWithDifferentProtectionSettings(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add($this->albumID1, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$this->albumID3 = $this->albums_tests->add($this->albumID1, TestConstants::ALBUM_TITLE_3)->offsetGet('id');
		$this->albumID4 = $this->albums_tests->add($this->albumID1, TestConstants::ALBUM_TITLE_4)->offsetGet('id');
		$this->albumID5 = $this->albums_tests->add($this->albumID1, TestConstants::ALBUM_TITLE_5)->offsetGet('id');
		$this->albumID6 = $this->albums_tests->add($this->albumID1, TestConstants::ALBUM_TITLE_6)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->photoID3 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID3)->offsetGet('id');
		$this->photoID4 = $this->photos_tests->upload(static::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $this->albumID4)->offsetGet('id');
		$this->photoID5 = $this->photos_tests->duplicate([$this->photoID4], $this->albumID5)->json()[0]['id'];
		$this->photoID6 = $this->photos_tests->duplicate([$this->photoID2], $this->albumID6)->json()[0]['id'];
		$this->photos_tests->set_title($this->photoID5, 'Abenddämmerung'); // we rename the duplicated images, such that we can ensure
		$this->photos_tests->set_title($this->photoID6, 'Zug'); // a deterministic, alphabetic order which makes testing easier

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID5);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID2, $this->photoID3, $this->photoID4, $this->photoID6);

		$this->albums_tests->set_protection_policy(id: $this->albumID1, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy(id: $this->albumID2);
		$this->albums_tests->set_protection_policy(id: $this->albumID3, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy(id: $this->albumID4, is_link_required: true, password: TestConstants::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy(id: $this->albumID5, password: TestConstants::ALBUM_PWD_2);
		$this->albums_tests->set_protection_policy(id: $this->albumID6, is_link_required: true, password: TestConstants::ALBUM_PWD_2);

		Auth::logout();
		Session::flush();
		$this->clearCachedSmartAlbums();
	}

	/**
	 * Tests scenario
	 * {@link SharingSpecialTest::prepareSixAlbumsWithDifferentProtectionSettings()}
	 * with the anonymous user and all albums being locked.
	 *
	 * The following results are expected:
	 *
	 *  - The root album shows album 1 but without a cover as album 1 is still
	 *    locked.
	 *  - The smart albums "Recent" and "On This Day" are empty
	 *  - The album tree does is empty
	 *    (TODO: Check if we want it this way)
	 *  - Album 2 and photo 2 are accessible; album 2 is public and behaves
	 *    like a hidden album
	 *    (TODO: Check if we want it this way)
	 *  - All other albums and photos are inaccessible
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettingsAndAllLocked(): void
	{
		$this->prepareSixAlbumsWithDifferentProtectionSettings();

		// 1. Check root album

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			[
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1), // no thumb as album 1 is still locked
			]
		));
		foreach ([
			$this->photoID1,
			$this->albumID2,
			$this->photoID2,
			$this->albumID3,
			$this->photoID3,
			$this->albumID4,
			$this->photoID4,
			$this->albumID5,
			$this->photoID5,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForRoot->assertJsonMissing(['id' => $id]);
		}

		// 2. Check "Recent" and "On This Day" albums

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		foreach ([
			$this->albumID1,
			$this->photoID1,
			$this->albumID2,
			$this->photoID2,
			$this->albumID3,
			$this->photoID3,
			$this->albumID4,
			$this->photoID4,
			$this->albumID5,
			$this->photoID5,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForRecent->assertJsonMissing(['id' => $id]);
		}

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(true));
		foreach ([
			$this->photoID1,
			$this->photoID2,
			$this->photoID3,
			$this->photoID4,
			$this->photoID5,
			$this->photoID6,
		] as $id) {
			$responseForOnThisDay->assertJsonMissing(['id' => $id]);
		}

		// 3. Check tree

		$responseForTree = $this->root_album_tests->getTree();
		// TODO: Should public and password-protected albums appear in tree? Regression?
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			// $this->generateExpectedAlbumJson($albumID1, TestConstants::ALBUM_TITLE_1), // no thumb as album 1 is still locked
		]));
		foreach ([
			$this->albumID1,
			$this->photoID1,
			$this->albumID2,
			$this->photoID2,
			$this->albumID3,
			$this->photoID3,
			$this->albumID4,
			$this->photoID4,
			$this->albumID5,
			$this->photoID5,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForTree->assertJsonMissing(['id' => $id]);
		}

		// 4. Check album/photo access

		foreach ([
			$this->albumID1,
			$this->albumID3,
			$this->albumID4,
			$this->albumID5,
			$this->albumID6,
		] as $id) {
			$this->albums_tests->get($id, 401, TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, TestConstants::EXPECTED_UNAUTHENTICATED_MSG);
		}
		foreach ([$this->photoID1, $this->photoID3, $this->photoID4, $this->photoID5, $this->photoID6] as $id) {
			$this->photos_tests->get($id, 401);
		}
		// Album 2 is a child of a password-protected, locked album and hence
		// it is not browsable.
		// However, it is public (without own password protection) and hence
		// directly accessible.
		// In other words, it behaves like a hidden album.
		// TODO: Do we want this that way?
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson($this->generateExpectedAlbumJson(
			$this->albumID2, TestConstants::ALBUM_TITLE_2, $this->albumID1, $this->photoID2
		));
		$this->photos_tests->get($this->photoID2);
	}

	/**
	 * Tests scenario
	 * {@link SharingSpecialTest::prepareSixAlbumsWithDifferentProtectionSettings()}
	 * with the anonymous user and albums with password 1 unlocked, but
	 * albums with password 2 still being locked.
	 *
	 * The following results are expected:
	 *
	 *  - The root album shows album 1 but with photo 3 as cover as
	 *    album 1-4 have been unlocked and photo 3 is alphabetically fist
	 *  - The smart album Recent shows photos 1-3; in particular photo 4
	 *    is not shown although it has been unlocked, too, but it is part of a
	 *    hidden album
	 *  - The smart album "On This Day" shows photo 1, since only photo 1 is
	 *    accessible and taken on this day
	 *  - The album tree shows album 1-3; it does not show album 5 as it is
	 *    password-protected and still locked
	 *    (TODO: Check if we want it this way)
	 *  - Albums 1-4 and photos 1-4 are accessible
	 *  - Albums 5+6 and photos 5+6 are still inaccessible
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettingsAndSomeUnlocked(): void
	{
		$this->prepareSixAlbumsWithDifferentProtectionSettings();
		$this->albums_tests->unlock($this->albumID1, TestConstants::ALBUM_PWD_1);

		// 1. Check root album

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID3,
			$this->photoID1, [ // albums 1-3 are accessible, photo 3 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID3), // thumb available as album 1 has been unlocked
			]
		));
		foreach ([
			$this->albumID2,
			$this->photoID2,
			$this->albumID3,
			$this->albumID4,
			$this->photoID4,
			$this->albumID5,
			$this->photoID5,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForRoot->assertJsonMissing(['id' => $id]);
		}

		// 2. Check "Recent" and "On This Day" albums

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID3, [ // albums 1-3 are accessible, photo 3 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		foreach ([$this->albumID4, $this->photoID4, $this->albumID5, $this->photoID5, $this->albumID6, $this->photoID6] as $id) {
			$responseForRecent->assertJsonMissing(['id' => $id]);
		}

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		foreach ([$this->photoID2, $this->photoID3, $this->photoID4, $this->photoID5, $this->photoID6] as $id) {
			$responseForOnThisDay->assertJsonMissing(['id' => $id]);
		}

		// 3. Check tree

		$responseForTree = $this->root_album_tests->getTree();
		// TODO: Should public and password-protected albums appear in tree? Regression?
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson(
				$this->albumID1,
				TestConstants::ALBUM_TITLE_1,
				null,
				$this->photoID3,
				['albums' => [
					$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, $this->albumID1, $this->photoID2),
					$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, $this->albumID1, $this->photoID3),
					// album 4 has been unlocked simultaneously with password 1, but is hidden and hence not part of the tree
					// $this->generateExpectedAlbumJson($albumID5, TestConstants::ALBUM_TITLE_5, $albumID1), // shown without thumb, as album 5 is still locked
					// album 5 is not part of the tree, because it protected by a different password and still locked; regression?
				]]
			),
		]));
		foreach ([
			$this->photoID1,
			$this->albumID4,
			$this->photoID4,
			$this->albumID5,
			$this->photoID5,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForTree->assertJsonMissing(['id' => $id]);
		}

		// 4. Check album/photo access

		foreach ([
			[$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID3], // photo 3 is alphabetically first of all child photos
			[$this->albumID2, TestConstants::ALBUM_TITLE_2, $this->albumID1, $this->photoID2],
			[$this->albumID3, TestConstants::ALBUM_TITLE_3, $this->albumID1, $this->photoID3],
			[$this->albumID4, TestConstants::ALBUM_TITLE_4, $this->albumID1, $this->photoID4], // album 4 has been unlocked simultaneously with password 1 and hence is directly accessible, although it is hidden
		] as $albumInfo) {
			$responseForAlbum = $this->albums_tests->get($albumInfo[0]);
			$responseForAlbum->assertJson($this->generateExpectedAlbumJson(
				$albumInfo[0], $albumInfo[1], $albumInfo[2], $albumInfo[3]
			));
		}
		foreach ([$this->albumID5, $this->albumID6] as $id) {
			$this->albums_tests->get($id, 401, TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG, TestConstants::EXPECTED_UNAUTHENTICATED_MSG);
		}

		foreach ([$this->photoID1, $this->photoID2, $this->photoID3, $this->photoID4] as $id) {
			$this->photos_tests->get($id);
		}
		foreach ([$this->photoID5, $this->photoID6] as $id) {
			$this->photos_tests->get($id, 401);
		}
	}

	/**
	 * Tests scenario
	 * {@link SharingSpecialTest::prepareSixAlbumsWithDifferentProtectionSettings()}
	 * with the anonymous user and all albums being unlocked.
	 *
	 * The following results are expected:
	 *
	 *  - The root album shows album 1 but with photo 5 as cover as
	 *    album 1-6 have been unlocked and photo 5 is alphabetically fist
	 *  - The smart album Recent shows photos 1-3+5; in particular photo 4+6
	 *    are not shown, although they have been unlocked, too, but they are
	 *    part of hidden albums
	 *  - Smart album "On This Day" shows photos 1 and 5 that were taken on this day
	 *  - The album tree shows album 1-3+5
	 *  - Albums 1-6 and photos 1-6 are accessible
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettingsAndAllUnlocked(): void
	{
		$this->prepareSixAlbumsWithDifferentProtectionSettings();
		$this->albums_tests->unlock($this->albumID1, TestConstants::ALBUM_PWD_1);
		$this->albums_tests->unlock($this->albumID5, TestConstants::ALBUM_PWD_2);

		// 1. Check root album

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID5,
			$this->photoID5, [ // albums 1-6 are accessible, photo 5 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID5), // thumb available as album 1 has been unlocked
			]
		));
		foreach ([$this->photoID1, $this->albumID2, $this->photoID2, $this->albumID3, $this->photoID3, $this->albumID4, $this->photoID4, $this->albumID5, $this->albumID6, $this->photoID6] as $id) {
			$responseForRoot->assertJsonMissing(['id' => $id]);
		}

		// 2. Check "Recent" and "On This Day" albums

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID5, [ // albums 1-3 and 5 are accessible, photo 5 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, $this->photoID5, $this->albumID5, ['title' => 'Abenddämmerung']),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		foreach ([$this->albumID4, $this->photoID4, $this->albumID6, $this->photoID6] as $id) {
			$responseForRecent->assertJsonMissing(['id' => $id]);
		}

		$responseForOnThisDayAlbum = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDayAlbum->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID5, [ // photos 1 and 5 were taken on this day and accessible, photo 5 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, $this->photoID5, $this->albumID5, ['title' => 'Abenddämmerung']),
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		foreach ([$this->photoID2, $this->photoID3, $this->photoID4, $this->photoID6] as $id) {
			$responseForOnThisDayAlbum->assertJsonMissing(['id' => $id]);
		}

		// 3. Check tree

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson(
				$this->albumID1,
				TestConstants::ALBUM_TITLE_1,
				null,
				$this->photoID5,
				['albums' => [
					$this->generateExpectedAlbumJson($this->albumID2, TestConstants::ALBUM_TITLE_2, $this->albumID1, $this->photoID2),
					$this->generateExpectedAlbumJson($this->albumID3, TestConstants::ALBUM_TITLE_3, $this->albumID1, $this->photoID3),
					// album 4 has been unlocked simultaneously with password 1, but is hidden and hence not part of the tree
					$this->generateExpectedAlbumJson($this->albumID5, TestConstants::ALBUM_TITLE_5, $this->albumID1, $this->photoID5),
					// album 6 has been unlocked simultaneously with password 2, but is hidden and hence not part of the tree
				]]
			),
		]));
		foreach ([
			$this->photoID1,
			$this->albumID4,
			$this->photoID4,
			$this->albumID6,
			$this->photoID6,
		] as $id) {
			$responseForTree->assertJsonMissing(['id' => $id]);
		}

		// 4. Check album/photo access

		foreach ([
			[$this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID5], // photo 5 is alphabetically first of all child photos
			[$this->albumID2, TestConstants::ALBUM_TITLE_2, $this->albumID1, $this->photoID2],
			[$this->albumID3, TestConstants::ALBUM_TITLE_3, $this->albumID1, $this->photoID3],
			[$this->albumID4, TestConstants::ALBUM_TITLE_4, $this->albumID1, $this->photoID4], // album 4 has been unlocked simultaneously with password 1 and hence is directly accessible, although it is hidden
			[$this->albumID5, TestConstants::ALBUM_TITLE_5, $this->albumID1, $this->photoID5],
			[$this->albumID6, TestConstants::ALBUM_TITLE_6, $this->albumID1, $this->photoID6], // album 6 has been unlocked simultaneously with password 2 and hence is directly accessible, although it is hidden
		] as $albumInfo) {
			$responseForAlbum = $this->albums_tests->get($albumInfo[0]);
			$responseForAlbum->assertJson($this->generateExpectedAlbumJson(
				$albumInfo[0], $albumInfo[1], $albumInfo[2], $albumInfo[3]
			));
		}
		foreach ([
			$this->photoID1,
			$this->photoID2,
			$this->photoID3,
			$this->photoID4,
			$this->photoID5,
			$this->photoID5,
		] as $id) {
			$this->photos_tests->get($id);
		}
	}

	protected function generateExpectedRootJson(
		?string $recentAlbumThumbID = null,
		?string $onThisDayAlbumThumbID = null,
		array $expectedAlbumJson = [],
	): array {
		return [
			'smart_albums' => [
				StarredAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
				OnThisDayAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($onThisDayAlbumThumbID)],
			],
			'tag_albums' => [],
			'albums' => $expectedAlbumJson,
			'shared_albums' => [],
		];
	}

	protected function generateExpectedTreeJson(array $expectedAlbums = []): array
	{
		return [
			'albums' => $expectedAlbums,
			'shared_albums' => [],
		];
	}

	protected function generateExpectedSmartAlbumJson(
		bool $isPublic,
		?string $thumbID = null,
		array $expectedPhotos = [],
	): array {
		return [
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'photos' => $expectedPhotos,
		];
	}
}
