<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Tests\Feature\Base\SharingTestBase;
use Tests\TestCase;

class SharingSpecialTest extends SharingTestBase
{
	public const ALBUM_TITLE_4 = 'Test Album 4';
	public const ALBUM_TITLE_5 = 'Test Album 5';
	public const ALBUM_TITLE_6 = 'Test Album 6';

	/**
	 * Tests six albums with one photo each and varying protection settings.
	 *
	 * This is the test for [Bug #1155](https://github.com/LycheeOrg/Lychee/issues/1155).
	 * Scenario:
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
	 * The anonymous user proceeds as follows:
	 *
	 *  1. Get root album view
	 *
	 *     _Expected result:_ Album A is visible, but without cover, it is still locked
	 *
	 *  2. Unlock albums with password "foo"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible without cover, as it is still locked
	 *      - Album F remains invisible
	 *
	 *  3. Directly access album D
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside D is visible as part of D, but nowhere else
	 *
	 *  4. Directly access album F
	 *
	 *     _Expected result:_ Access is denied
	 *
	 *  5. Unlock albums with password "bar"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible with cover, as it became unlocked simultaneously
	 *      - Album F remains invisible
	 *
	 *  6. Directly access album F
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside F is visible as part of F, but nowhere else
	 *
	 * In particular, each visibility check includes
	 *  - the content inside the album itself
	 *  - the album "Recent"
	 *  - the album tree
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettings(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);

		// PREPARATION

		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_2)->offsetGet('id');
		$albumID3 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_3)->offsetGet('id');
		$albumID4 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_4)->offsetGet('id');
		$albumID5 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_5)->offsetGet('id');
		$albumID6 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_6)->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_NIGHT_IMAGE), $albumID1)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID2)->offsetGet('id');
		$photoID3 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID3)->offsetGet('id');
		$photoID4 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_SUNSET_IMAGE), $albumID4)->offsetGet('id');
		$photoID5 = $this->photos_tests->duplicate([$photoID3], $albumID5);
		$photoID6 = $this->photos_tests->duplicate([$photoID2], $albumID6);

		$this->albums_tests->set_protection_policy($albumID1, true, true, false, false, true, true, self::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy($albumID2);
		$this->albums_tests->set_protection_policy($albumID3, true, true, false, false, true, true, self::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy($albumID4, true, true, true, false, true, true, self::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy($albumID5, true, true, false, false, true, true, self::ALBUM_PWD_2);
		$this->albums_tests->set_protection_policy($albumID6, true, true, true, false, true, true, self::ALBUM_PWD_2);

		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		// EVERYTHING IS LOCKED

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null, [
				$this->generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1), // no thumb as album 1 is still locked
			]
		));
		foreach ([$photoID1, $albumID2, $photoID2, $albumID3, $photoID3, $albumID4, $photoID4, $albumID5, $photoID5, $albumID6, $photoID6] as $id) {
			$responseForRoot->assertJsonMissing(['id' => $id]);
		}

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		foreach ([$albumID1, $photoID1, $albumID2, $photoID2, $albumID3, $photoID3, $albumID4, $photoID4, $albumID5, $photoID5, $albumID6, $photoID6] as $id) {
			$responseForRecent->assertJsonMissing(['id' => $id]);
		}

		$responseForTree = $this->root_album_tests->getTree();
		// TODO: Should public and password-protected albums appear in tree? Regression?
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			// $this->generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1), // no thumb as album 1 is still locked
		]));
		foreach ([$albumID1, $photoID1, $albumID2, $photoID2, $albumID3, $photoID3, $albumID4, $photoID4, $albumID5, $photoID5, $albumID6, $photoID6] as $id) {
			$responseForTree->assertJsonMissing(['id' => $id]);
		}

		// UNLOCK ALBUM 1 (and ALBUM 3 SIMULTANEOUSLY)

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
		static::markTestIncomplete('Not written yet');
	}

	protected function generateExpectedRootJson(
		?string $recentAlbumThumbID = null,
		array $expectedAlbumJson = []
	): array {
		return [
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
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
}
