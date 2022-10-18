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

use App\Models\Configs;
use Tests\Feature\Lib\RootAlbumUnitTest;
use Tests\Feature\Lib\SharingUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\Feature\Traits\InteractWithSmartAlbums;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\TestCase;

class SharingTestBase extends PhotoTestBase
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use InteractWithSmartAlbums;

	public const PHOTO_NIGHT_TITLE = 'night';
	public const PHOTO_MONGOLIA_TITLE = 'mongolia';
	public const PHOTO_SUNSET_TITLE = 'fin de journée';
	public const PHOTO_TRAIN_TITLE = 'train';

	public const ALBUM_TITLE_1 = 'Test Album 1';
	public const ALBUM_TITLE_2 = 'Test Album 2';
	public const ALBUM_TITLE_3 = 'Test Album 3';

	public const ALBUM_PWD_1 = 'Album Password 1';
	public const ALBUM_PWD_2 = 'Album Password 2';
	public const ALBUM_PWD_3 = 'Album Password 3';

	public const USER_NAME_1 = 'Test User 1';
	public const USER_NAME_2 = 'Test User 2';
	public const USER_NAME_3 = 'Test User 3';

	public const USER_PWD_1 = 'User Password 1';
	public const USER_PWD_2 = 'User Password 2';
	public const USER_PWD_3 = 'User Password 3';

	/** @var array[] defines the expected JSON result for each sample image such that we can avoid repeating this again and again during the tests */
	public const EXPECTED_PHOTO_JSON = [
		TestCase::SAMPLE_FILE_NIGHT_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_NIGHT_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 6720, 'height' => 4480],
				'medium2x' => ['type' => 1, 'width' => 3240, 'height' => 2160],
				'medium' => ['type' => 2, 'width' => 1620, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 1080,	'height' => 720],
				'small' => ['type' => 4, 'width' => 540, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_MONGOLIA_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_MONGOLIA_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 1280, 'height' => 850],
				'medium2x' => null,
				'medium' => null,
				'small2x' => ['type' => 3, 'width' => 1084,	'height' => 720],
				'small' => ['type' => 4, 'width' => 542, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_SUNSET_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_SUNSET_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 914, 'height' => 1625],
				'medium2x' => null,
				'medium' => ['type' => 2, 'width' => 607, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 405,	'height' => 720],
				'small' => ['type' => 4, 'width' => 202, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_TRAIN_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_TRAIN_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 4032, 'height' => 3024],
				'medium2x' => ['type' => 1, 'width' => 2880, 'height' => 2160],
				'medium' => ['type' => 2, 'width' => 1440, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 960,	'height' => 720],
				'small' => ['type' => 4, 'width' => 480, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_PSD => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_TRAIN_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 4032, 'height' => 3024],
				'medium2x' => ['type' => 1, 'width' => 2880, 'height' => 2160],
				'medium' => ['type' => 2, 'width' => 1440, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 960,	'height' => 720],
				'small' => ['type' => 4, 'width' => 480, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
	];

	public const EXPECTED_UNAUTHENTICATED_MSG = 'User is not authenticated';
	public const EXPECTED_FORBIDDEN_MSG = 'Insufficient privileges';
	public const EXPECTED_PASSWORD_REQUIRED_MSG = 'Password required';

	protected SharingUnitTest $sharing_tests;
	protected UsersUnitTest $users_tests;
	protected RootAlbumUnitTest $root_album_tests;

	/** @var string the previously configured column for album sorting */
	private string $albumsSortingCol;

	/** @var string the previously configured order for album sorting */
	private string $albumsSortingOrder;

	/** @var string the previously configured column for photo sorting */
	private string $photosSortingCol;

	/** @var string the previously configured order for photo sorting */
	private string $photosSortingOrder;

	/** @var bool the previously configured public visibility for "Recent" */
	private bool $isRecentAlbumPublic;

	/** @var bool the previously configured public visibility for "Starred" */
	private bool $isStarredAlbumPublic;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyUsers();
		$this->sharing_tests = new SharingUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);

		// We must ensure a specific order of photos and albums otherwise
		// the test cannot reliably compare actual and expected values.
		// We sort by title, because tests are running so fast that using
		// creation time is not reliable as models get identical timestamps.
		// (This is not so much a problem for photos as photo processing
		// takes some time, but it is a problem for albums.)
		$this->albumsSortingCol = Configs::getValueAsString(TestCase::CONFIG_ALBUMS_SORTING_COL);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_COL, 'title');
		$this->albumsSortingOrder = Configs::getValueAsString(TestCase::CONFIG_ALBUMS_SORTING_ORDER);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');
		$this->photosSortingCol = Configs::getValueAsString(TestCase::CONFIG_PHOTOS_SORTING_COL);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_COL, 'title');
		$this->photosSortingOrder = Configs::getValueAsString(TestCase::CONFIG_PHOTOS_SORTING_ORDER);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_ORDER, 'ASC');

		$this->isRecentAlbumPublic = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_RECENT);
		Configs::set(TestCase::CONFIG_PUBLIC_RECENT, true);
		$this->isStarredAlbumPublic = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_STARRED);
		Configs::set(TestCase::CONFIG_PUBLIC_STARRED, true);
		$this->clearCachedSmartAlbums();
	}

	public function tearDown(): void
	{
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_COL, $this->albumsSortingCol);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_ORDER, $this->albumsSortingOrder);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_COL, $this->photosSortingCol);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_ORDER, $this->photosSortingOrder);

		Configs::set(TestCase::CONFIG_PUBLIC_RECENT, $this->isRecentAlbumPublic);
		Configs::set(TestCase::CONFIG_PUBLIC_STARRED, $this->isStarredAlbumPublic);
		$this->clearCachedSmartAlbums();

		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * Returns a JSON description of a photo.
	 *
	 * This is an internal helper method to avoid repeating the same
	 * cumbersome, expected JSON description again and again.
	 *
	 * @param string      $samplePhotoID the identifier of the sample photo:
	 *                                   {@link TestCase::SAMPLE_FILE_NIGHT_IMAGE},
	 *                                   {@link TestCase::SAMPLE_FILE_MONGOLIA_IMAGE}, or
	 *                                   {@link TestCase::SAMPLE_FILE_TRAIN_IMAGE}
	 * @param string      $photoID       the photo ID
	 * @param string|null $albumID       the album ID
	 * @param array       $attrToMerge   additional attributes which should be
	 *                                   merged into the generated JSON
	 *
	 * @return array
	 */
	protected function generateExpectedPhotoJson(string $samplePhotoID, string $photoID, ?string $albumID, array $attrToMerge = []): array
	{
		$json = self::EXPECTED_PHOTO_JSON[$samplePhotoID];
		$json['id'] = $photoID;
		$json['album_id'] = $albumID;

		return array_replace_recursive($json, $attrToMerge);
	}

	protected function generateExpectedThumbJson(?string $photoID): array|null
	{
		return $photoID === null ? null : ['id' => $photoID, 'type' => 'image/jpeg'];
	}

	protected function generateExpectedAlbumJson(string $albumID, string $albumTitle, ?string $parentAlbumID = null, ?string $thumbID = null, array $attrToMerge = []): array
	{
		return array_replace_recursive([
			'id' => $albumID,
			'title' => $albumTitle,
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'parent_id' => $parentAlbumID,
		], $attrToMerge);
	}

	protected function generateExpectedSmartAlbumJson(
		bool $isPublic,
		?string $thumbID = null,
		array $expectedPhotos = []
	): array {
		return [
			'is_public' => $isPublic,
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'photos' => $expectedPhotos,
		];
	}
}
