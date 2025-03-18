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
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photo_i_d1, 'photo search');
		$this->photos_tests->set_title($photo_i_d2, 'do not find me');

		$response = $this->runSearch('search');

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photo_i_d1,
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
			'id' => $photo_i_d2,
		]);
	}

	public function testSearchPhotoByTag(): void
	{
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photo_i_d1, 'photo search');
		$this->photos_tests->set_title($photo_i_d2, 'do not find me');
		$this->photos_tests->set_tag([$photo_i_d1], ['search tag']);
		$this->photos_tests->set_tag([$photo_i_d2], ['other tag']);

		$response = $this->runSearch('search');

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photo_i_d1,
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
			'id' => $photo_i_d2,
		]);
	}

	public function testSearchAlbumByTitle(): void
	{
		/** @var string $albumID1 */
		$album_i_d1 = $this->albums_tests->add(null, 'search')->offsetGet('id');
		/** @var string $albumID2 */
		$album_i_d2 = $this->albums_tests->add(null, 'other')->offsetGet('id');

		$response = $this->runSearch('search');

		$response->assertJson([
			'albums' => [[
				'id' => $album_i_d1,
				'title' => 'search',
			]],
		]);

		$response->assertJsonMissing([
			'title' => 'other',
		]);

		$response->assertJsonMissing([
			'id' => $album_i_d2,
		]);
	}

	public function testDisabledPublicSearchWithAnonUser(): void
	{
		$is_public_search_enabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, false);
			$this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			Auth::logout();
			Session::flush();
			$this->runSearch('Matching', 401);
		} finally {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $is_public_search_enabled);
		}
	}

	public function testSearchAlbumByTitleWithAnonUser(): void
	{
		$is_public_search_enabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);

			$album_i_d1 = $this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			$album_i_d2 = $this->albums_tests->add(null, 'Matching shared album')->offsetGet('id');
			$album_i_d3 = $this->albums_tests->add(null, 'Matching public album')->offsetGet('id');
			$album_i_d4 = $this->albums_tests->add(null, 'Other public album')->offsetGet('id');
			$user_i_d = $this->users_tests->add('Test User', 'Test password')->offsetGet('id');
			$this->sharing_test->add([$album_i_d2], [$user_i_d]);
			$this->albums_tests->set_protection_policy($album_i_d3);
			$this->albums_tests->set_protection_policy($album_i_d4);

			Auth::logout();
			Session::flush();

			$response = $this->runSearch('Matching');
			$response->assertJson([
				'albums' => [[
					'id' => $album_i_d3,
					'title' => 'Matching public album',
				]],
			]);

			$response->assertJsonMissing(['title' => 'Matching private album']);
			$response->assertJsonMissing(['title' => 'Matching shared album']);
			$response->assertJsonMissing(['title' => 'Other public album']);
			$response->assertJsonMissing(['id' => $album_i_d1]);
			$response->assertJsonMissing(['id' => $album_i_d2]);
			$response->assertJsonMissing(['id' => $album_i_d4]);
		} finally {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $is_public_search_enabled);
		}
	}

	public function testSearchAlbumByTitleWithNonAdminUser(): void
	{
		$is_public_search_enabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		$album_sorting_column = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		$album_sorting_order = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);
		try {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');

			$album_i_d1 = $this->albums_tests->add(null, 'Matching private album')->offsetGet('id');
			$album_i_d2 = $this->albums_tests->add(null, 'Matching shared album')->offsetGet('id');
			$album_i_d3 = $this->albums_tests->add(null, 'Matching public album')->offsetGet('id');
			$album_i_d4 = $this->albums_tests->add(null, 'Other public album')->offsetGet('id');
			$user_i_d = $this->users_tests->add('Test User', 'Test password')->offsetGet('id');
			$this->sharing_test->add([$album_i_d2], [$user_i_d]);
			$this->albums_tests->set_protection_policy($album_i_d3);
			$this->albums_tests->set_protection_policy($album_i_d4);

			Auth::logout();
			Session::flush();
			Auth::loginUsingId($user_i_d);

			$response = $this->runSearch('Matching');
			$response->assertJson([
				'albums' => [[
					'id' => $album_i_d3,
					'title' => 'Matching public album',
				], [
					'id' => $album_i_d2,
					'title' => 'Matching shared album',
				]],
			]);

			$response->assertJsonMissing(['title' => 'Matching private album']);
			$response->assertJsonMissing(['title' => 'Other public album']);
			$response->assertJsonMissing(['id' => $album_i_d1]);
			$response->assertJsonMissing(['id' => $album_i_d4]);
		} finally {
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $album_sorting_column);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $album_sorting_order);
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $is_public_search_enabled);
		}
	}

	public function testSearchTagAlbumByTitle(): void
	{
		/** @var string $tagAlbumId1 */
		$tag_album_id1 = $this->albums_tests->addByTags('tag search', ['tag1', 'tag2'])->offsetGet('id');
		/** @var string $tagAlbumId2 */
		$tag_album_id2 = $this->albums_tests->addByTags('tag other', ['tag3'])->offsetGet('id');

		$response = $this->runSearch('search');

		$response->assertJson([
			'tag_albums' => [[
				'id' => $tag_album_id1,
				'title' => 'tag search',
			]],
		]);

		$response->assertJsonMissing([
			'title' => 'tag other',
		]);

		$response->assertJsonMissing([
			'id' => $tag_album_id2,
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
		int $expected_status_code = 200,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->postJson(
			'/api/Search::run',
			['term' => $term]
		);
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}
}
