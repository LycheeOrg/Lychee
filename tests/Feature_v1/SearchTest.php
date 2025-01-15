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

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Testing\TestResponse;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

class SearchTest extends BasePhotoTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;

	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_test;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyUsers();
		$this->users_tests = new UsersUnitTest($this);
		$this->sharing_test = new SharingUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testSearchPhotoByTitle(): void
	{
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photoID1, 'photo search');
		$this->photos_tests->set_title($photoID2, 'do not find me');

		$response = $this->runSearch('search');

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photoID1,
					'iso' => '1250',
					'lens' => 'EF16-35mm f/2.8L USM',
					'make' => 'Canon',
					'model' => 'Canon EOS R',
					'shutter' => '30 s',
					'title' => 'photo search',
					'type' => 'image/jpeg',
					'size_variants' => [
						'small' => [
							'width' => 540,
							'height' => 360,
						],
						'medium' => [
							'width' => 1620,
							'height' => 1080,
						],
						'original' => [
							'width' => 6720,
							'height' => 4480,
							'filesize' => 21106422,
						],
					],
				],
			],
		]);

		$response->assertJsonMissing([
			'title' => 'do not find me',
		]);

		$response->assertJsonMissing([
			'id' => $photoID2,
		]);
	}

	public function testSearchPhotoByTag(): void
	{
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photoID1, 'photo search');
		$this->photos_tests->set_title($photoID2, 'do not find me');
		$this->photos_tests->set_tag([$photoID1], ['search tag']);
		$this->photos_tests->set_tag([$photoID2], ['other tag']);

		$response = $this->runSearch('search');

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photoID1,
					'iso' => '1250',
					'lens' => 'EF16-35mm f/2.8L USM',
					'make' => 'Canon',
					'model' => 'Canon EOS R',
					'shutter' => '30 s',
					'tags' => ['search tag'],
					'type' => 'image/jpeg',
					'size_variants' => [
						'small' => [
							'width' => 540,
							'height' => 360,
						],
						'medium' => [
							'width' => 1620,
							'height' => 1080,
						],
						'original' => [
							'width' => 6720,
							'height' => 4480,
							'filesize' => 21106422,
						],
					],
				],
			],
		]);

		$response->assertJsonMissing([
			'title' => 'do not find me',
		]);

		$response->assertJsonMissing([
			'tags' => ['other tag'],
		]);

		$response->assertJsonMissing([
			'id' => $photoID2,
		]);
	}

	public function testSearchAlbumByTitle(): void
	{
		/** @var string $albumID1 */
		$albumID1 = $this->albums_tests->add(null, 'search')->offsetGet('id');
		/** @var string $albumID2 */
		$albumID2 = $this->albums_tests->add(null, 'other')->offsetGet('id');

		$response = $this->runSearch('search');

		$response->assertJson([
			'albums' => [[
				'id' => $albumID1,
				'title' => 'search',
			]],
		]);

		$response->assertJsonMissing([
			'title' => 'other',
		]);

		$response->assertJsonMissing([
			'id' => $albumID2,
		]);
	}

	public function testDisabledPublicSearchWithAnonUser(): void
	{
		$isPublicSearchEnabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, false);
			$this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			Auth::logout();
			Session::flush();
			$this->runSearch('Matching', 401);
		} finally {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $isPublicSearchEnabled);
		}
	}

	public function testSearchAlbumByTitleWithAnonUser(): void
	{
		$isPublicSearchEnabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);

			$albumID1 = $this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			$albumID2 = $this->albums_tests->add(null, 'Matching shared album')->offsetGet('id');
			$albumID3 = $this->albums_tests->add(null, 'Matching public album')->offsetGet('id');
			$albumID4 = $this->albums_tests->add(null, 'Other public album')->offsetGet('id');
			$userID = $this->users_tests->add('Test User', 'Test password')->offsetGet('id');
			$this->sharing_test->add([$albumID2], [$userID]);
			$this->albums_tests->set_protection_policy($albumID3);
			$this->albums_tests->set_protection_policy($albumID4);

			Auth::logout();
			Session::flush();

			$response = $this->runSearch('Matching');
			$response->assertJson([
				'albums' => [[
					'id' => $albumID3,
					'title' => 'Matching public album',
				]],
			]);

			$response->assertJsonMissing(['title' => 'Matching private album']);
			$response->assertJsonMissing(['title' => 'Matching shared album']);
			$response->assertJsonMissing(['title' => 'Other public album']);
			$response->assertJsonMissing(['id' => $albumID1]);
			$response->assertJsonMissing(['id' => $albumID2]);
			$response->assertJsonMissing(['id' => $albumID4]);
		} finally {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $isPublicSearchEnabled);
		}
	}

	public function testSearchAlbumByTitleWithNonAdminUser(): void
	{
		$isPublicSearchEnabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		$albumSortingColumn = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		$albumSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');

			$albumID1 = $this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			$albumID2 = $this->albums_tests->add(null, 'Matching shared album')->offsetGet('id');
			$albumID3 = $this->albums_tests->add(null, 'Matching public album')->offsetGet('id');
			$albumID4 = $this->albums_tests->add(null, 'Other public album')->offsetGet('id');
			$userID = $this->users_tests->add('Test User', 'Test password')->offsetGet('id');
			$this->sharing_test->add([$albumID2], [$userID]);
			$this->albums_tests->set_protection_policy($albumID3);
			$this->albums_tests->set_protection_policy($albumID4);

			Auth::logout();
			Session::flush();
			Auth::loginUsingId($userID);

			$response = $this->runSearch('Matching');
			$response->assertJson([
				'albums' => [[
					'id' => $albumID3,
					'title' => 'Matching public album',
				], [
					'id' => $albumID2,
					'title' => 'Matching shared album',
				]],
			]);

			$response->assertJsonMissing(['title' => 'Matching private album']);
			$response->assertJsonMissing(['title' => 'Other public album']);
			$response->assertJsonMissing(['id' => $albumID1]);
			$response->assertJsonMissing(['id' => $albumID4]);
		} finally {
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $albumSortingColumn);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $albumSortingOrder);
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $isPublicSearchEnabled);
		}
	}

	public function testSearchTagAlbumByTitle(): void
	{
		/** @var string $tagAlbumId1 */
		$tagAlbumId1 = $this->albums_tests->addByTags('tag search', ['tag1', 'tag2'])->offsetGet('id');
		/** @var string $tagAlbumId2 */
		$tagAlbumId2 = $this->albums_tests->addByTags('tag other', ['tag3'])->offsetGet('id');

		$response = $this->runSearch('search');

		$response->assertJson([
			'tag_albums' => [[
				'id' => $tagAlbumId1,
				'title' => 'tag search',
			]],
		]);

		$response->assertJsonMissing([
			'title' => 'tag other',
		]);

		$response->assertJsonMissing([
			'id' => $tagAlbumId2,
		]);
	}

	/**
	 * Runs the search.
	 *
	 * @param string      $term
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	protected function runSearch(
		string $term,
		int $expectedStatusCode = 200,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->postJson(
			'/api/Search::run',
			['term' => $term]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
