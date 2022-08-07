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

namespace Tests\Feature\Base;

use App\Facades\AccessControl;
use App\Models\Configs;
use Tests\TestCase;

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
 *  - {@link \Tests\Feature\SharingWithAnonUserAndNoPublicSearchTest}
 *  - {@link \Tests\Feature\SharingWithAnonUserAndPublicSearchTest}
 *  - {@link \Tests\Feature\SharingWithNonAdminUserAndNoPublicSearchTest}
 *  - {@link \Tests\Feature\SharingWithNonAdminUserAndPublicSearchTest}
 *
 * Note, the 2nd dimension could be extended by further options, e.g. a
 * non-admin user with whom the object has not been shared and a non-admin
 * user which is the owner of the object.
 * Moreover, there are further potential dimensions, e.g. options like
 * whether albums are downloadable by default, etc. pp.
 */
abstract class SharingTestScenariosAbstract extends SharingTestBase
{
	/** @var bool the previously configured visibility for public photos */
	protected bool $arePublicPhotosHidden;

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

		$this->arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);

		// Reset all variables to ensure that a test implementation of the
		// child class does not accidentally use a value which it should not.
		$this->albumID1 = null;
		$this->albumID2 = null;
		$this->albumID3 = null;
		$this->photoID1 = null;
		$this->photoID2 = null;
		$this->photoID3 = null;
		$this->userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
	}

	public function tearDown(): void
	{
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $this->arePublicPhotosHidden);
		parent::tearDown();
	}

	/**
	 * Uploads an unsorted, private photo and logs out.
	 *
	 * @return void
	 */
	protected function prepareUnsortedPrivatePhoto(): void
	{
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testUnsortedPrivatePhoto(): void;

	/**
	 * Uploads two unsorted photos, marks the first photo as public, stars it
	 * and logs out.
	 *
	 * @return void
	 */
	protected function prepareUnsortedPublicAndPrivatePhoto(): void
	{
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		$this->photos_tests->set_public($this->photoID1, true);
		$this->photos_tests->set_star([$this->photoID1], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testUnsortedPublicAndPrivatePhoto(): void;

	/**
	 * Creates an album with two photos, marks the first photo as public stars
	 * it and logs out.
	 *
	 * This scenario is similar to
	 * {@link SharingTestScenariosAbstract::prepareUnsortedPublicAndPrivatePhoto()}
	 * but with an album.
	 *
	 * @return void
	 */
	protected function preparePublicAndPrivatePhotoInPrivateAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photos_tests->set_public($this->photoID1, true);
		$this->photos_tests->set_star([$this->photoID1], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testPublicAndPrivatePhotoInPrivateAlbum(): void;

	/**
	 * Creates an album with two photos, marks the album as public and stars
	 * the alphabetically last photo as public and logs out.
	 *
	 * @return void
	 */
	protected function prepareTwoPhotosInPublicAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->albums_tests->set_protection_policy($this->albumID1);
		$this->photos_tests->set_star([$this->photoID1], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testTwoPhotosInPublicAlbum(): void;

	/**
	 * Uploads an unsorted photo and another photo into an album, marks the
	 * unsorted photo as public and stars it, shares the album with a
	 * non-admin user, stars the phot inside the shared album and logs out.
	 *
	 * @return void
	 */
	protected function preparePublicPhotoAndPhotoInSharedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->sharing_tests->add([$this->albumID1], [$this->userID]);
		$this->photos_tests->set_public($this->photoID1, true);
		$this->photos_tests->set_star([$this->photoID1, $this->photoID2], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testPublicPhotoAndPhotoInSharedAlbum(): void;

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as password-protected and
	 * logs out.
	 *
	 * Checks that the anonymous user only sees both albums,
	 * but only the cover of the public one, provide password, checks that
	 * now both covers are visible.
	 *
	 * In particular the following checks are made:
	 *  - before the password has been provided the anonymous user only sees
	 *    the public photo
	 *     - as a cover of the public album
	 *     - in "Recent"
	 *     - in the album tree
	 *  - after the password has been provided the anonymous user sees both
	 *    photos
	 *     - as covers
	 *     - in "Recent"
	 *     - in the album tree
	 *
	 * @return void
	 */
	protected function preparePublicAlbumAndPasswordProtectedAlbum(): void
	{
		$this->albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$this->albumID2 = $this->albums_tests->add(null, self::ALBUM_TITLE_2)->offsetGet('id');
		$this->photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $this->albumID1)->offsetGet('id');
		$this->photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $this->albumID2)->offsetGet('id');
		$this->albums_tests->set_protection_policy($this->albumID1, true, true, false, false, true, true, self::ALBUM_PWD_1);
		$this->albums_tests->set_protection_policy($this->albumID2);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();
	}

	abstract public function testPublicAlbumAndPasswordProtectedAlbum(): void;
}
