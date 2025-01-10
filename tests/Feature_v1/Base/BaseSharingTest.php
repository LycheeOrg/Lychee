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

namespace Tests\Feature_v1\Base;

use App\Models\Configs;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\RootAlbumUnitTest;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

abstract class BaseSharingTest extends BasePhotoTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use InteractWithSmartAlbums;

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

	/** @var bool the previously configured public visibility for "On This Day" */
	private bool $isOnThisDayAlbumPublic;

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
		$this->albumsSortingCol = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
		$this->albumsSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);
		Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');
		$this->photosSortingCol = Configs::getValueAsString(TestConstants::CONFIG_PHOTOS_SORTING_COL);
		Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_COL, 'title');
		$this->photosSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_PHOTOS_SORTING_ORDER);
		Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_ORDER, 'ASC');

		$this->isRecentAlbumPublic = RecentAlbum::getInstance()->public_permissions() !== null;
		RecentAlbum::getInstance()->setPublic();
		$this->isStarredAlbumPublic = StarredAlbum::getInstance()->public_permissions() !== null;
		StarredAlbum::getInstance()->setPublic();
		$this->isOnThisDayAlbumPublic = OnThisDayAlbum::getInstance()->public_permissions() !== null;
		OnThisDayAlbum::getInstance()->setPublic();
		$this->clearCachedSmartAlbums();
	}

	public function tearDown(): void
	{
		Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $this->albumsSortingCol);
		Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $this->albumsSortingOrder);
		Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_COL, $this->photosSortingCol);
		Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_ORDER, $this->photosSortingOrder);

		if ($this->isRecentAlbumPublic) {
			RecentAlbum::getInstance()->setPublic();
		} else {
			RecentAlbum::getInstance()->setPrivate();
		}

		if ($this->isStarredAlbumPublic) {
			StarredAlbum::getInstance()->setPublic();
		} else {
			StarredAlbum::getInstance()->setPrivate();
		}

		if ($this->isOnThisDayAlbumPublic) {
			OnThisDayAlbum::getInstance()->setPublic();
		} else {
			OnThisDayAlbum::getInstance()->setPrivate();
		}
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
	 *                                   {@link Tests\AbstractTestCase::SAMPLE_FILE_NIGHT_IMAGE},
	 *                                   {@link Tests\AbstractTestCase::SAMPLE_FILE_MONGOLIA_IMAGE}, or
	 *                                   {@link Tests\AbstractTestCase::SAMPLE_FILE_TRAIN_IMAGE}
	 * @param string      $photoID       the photo ID
	 * @param string|null $albumID       the album ID
	 * @param array       $attrToMerge   additional attributes which should be
	 *                                   merged into the generated JSON
	 *
	 * @return array
	 */
	protected function generateExpectedPhotoJson(string $samplePhotoID, string $photoID, ?string $albumID, array $attrToMerge = []): array
	{
		$json = TestConstants::EXPECTED_PHOTO_JSON[$samplePhotoID];
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
		array $expectedPhotos = [],
	): array {
		return [
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'photos' => $expectedPhotos,
			'policy' => ['is_public' => $isPublic],
		];
	}

	protected function ensurePhotosWereTakenOnThisDay(string ...$photoIDs): void
	{
		DB::table('photos')
			->whereIn('id', $photoIDs)
			->update(['taken_at' => (Carbon::today())->subYear()->format('Y-m-d H:i:s.u')]);
	}

	protected function ensurePhotosWereNotTakenOnThisDay(string ...$photoIDs): void
	{
		DB::table('photos')
			->whereIn('id', $photoIDs)
			->update(['taken_at' => (Carbon::today())->subMonth()->format('Y-m-d H:i:s.u')]);
	}
}
