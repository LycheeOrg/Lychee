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

namespace Tests\Feature_v1\LibUnitTests;

use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\AbstractTestCase;
use Tests\Traits\CatchFailures;

class AlbumsUnitTest
{
	use CatchFailures;

	private AbstractTestCase $testCase;

	public function __construct(AbstractTestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * Add an album.
	 *
	 * @param string|null $parent_id
	 * @param string      $title
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function add(
		?string $parent_id,
		string $title,
		int $expectedStatusCode = 201,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Album::add', [
			'title' => $title,
			'parent_id' => $parent_id,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Add an album.
	 *
	 * @param string      $title
	 * @param string[]    $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function addByTags(
		string $title,
		array $tags,
		int $expectedStatusCode = 201,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Album::addByTags', [
			'title' => $title,
			'tags' => $tags,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Move albums.
	 *
	 * @param string[]    $ids
	 * @param string|null $to
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function move(
		array $ids,
		?string $to,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::move', [
			'albumID' => $to,
			'albumIDs' => $ids,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Move albums.
	 *
	 * @param string[]    $ids
	 * @param string|null $to
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function merge(
		array $ids,
		?string $to,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::merge', [
			'albumID' => $to,
			'albumIDs' => $ids,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Get album by ID.
	 *
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 * @param string|null $assertDontSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function get(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null,
		?string $assertDontSee = null,
	): TestResponse {
		$response = $this->testCase->postJson(
			'/api/Album::get',
			['albumID' => $id]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
		if ($assertDontSee !== null) {
			$response->assertDontSee($assertDontSee, false);
		}

		return $response;
	}

	/**
	 * @param string      $id
	 * @param string      $password
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function unlock(
		string $id,
		string $password = '',
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::unlock',
			['albumID' => $id, 'password' => $password]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Change title.
	 *
	 * @param string      $id
	 * @param string      $title
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_title(
		string $id,
		string $title,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::setTitle',
			['albumIDs' => [$id], 'title' => $title]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Change copyright.
	 *
	 * @param string      $id
	 * @param string      $copyright
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_copyright(
		string $id,
		string $copyright,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::setCopyright',
			['albumID' => $id, 'copyright' => $copyright]
		);

		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Change description.
	 *
	 * @param string      $id
	 * @param string      $description
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_description(
		string $id,
		string $description,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::setDescription',
			['albumID' => $id, 'description' => $description]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Change cover.
	 *
	 * @param string      $id
	 * @param string|null $photoID
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_cover(
		string $id,
		?string $photoID,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::setCover',
			['albumID' => $id, 'photoID' => $photoID]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Change header.
	 *
	 * @param string      $id
	 * @param string|null $photoID
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_header(
		string $id,
		?string $photoID,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson(
			'/api/Album::setHeader',
			['albumID' => $id, 'photoID' => $photoID]
		);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Set the licence.
	 *
	 * @param string      $id
	 * @param string      $license
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_license(
		string $id,
		string $license,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::setLicense', [
			'albumID' => $id,
			'license' => $license,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Set sorting.
	 *
	 * @param string      $id
	 * @param string      $sortingCol
	 * @param string      $sortingOrder
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_sorting(
		string $id,
		string $sortingCol,
		string $sortingOrder,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::setSorting', [
			'albumID' => $id,
			'sorting_column' => $sortingCol,
			'sorting_order' => $sortingOrder,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * @param string      $id
	 * @param bool        $grants_full_photo_access
	 * @param bool        $is_public
	 * @param bool        $is_link_required
	 * @param bool        $is_nsfw
	 * @param bool        $grants_downloadable
	 * @param string|null $password                 `null` does not change password
	 *                                              settings;
	 *                                              the empty string `''` removes
	 *                                              a (potentially set) password;
	 *                                              a non-empty string sets the
	 *                                              password accordingly
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_protection_policy(
		string $id,
		bool $grants_full_photo_access = true,
		bool $is_public = true,
		bool $is_link_required = false,
		bool $is_nsfw = false,
		bool $grants_downloadable = true,
		?string $password = null,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$params = [
			'grants_full_photo_access' => $grants_full_photo_access,
			'albumID' => $id,
			'is_public' => $is_public,
			'is_link_required' => $is_link_required,
			'is_nsfw' => $is_nsfw,
			'grants_download' => $grants_downloadable,
		];

		if ($password !== null) {
			$params['password'] = $password;
		}

		$response = $this->testCase->postJson('/api/Album::setProtectionPolicy', $params);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * @param string      $id
	 * @param string[]    $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_tags(
		string $id,
		array $tags,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::setShowTags', [
			'albumID' => $id,
			'show_tags' => $tags,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * We only test for a code 200.
	 *
	 * @param string $id
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function download(string $id): TestResponse
	{
		$response = $this->testCase->getWithParameters(
			'/api/Album::getArchive', [
				'albumIDs' => $id,
			], [
				'Accept' => '*/*',
			]
		);
		$this->assertOk($response);
		if ($response->baseResponse instanceof StreamedResponse) {
			// The content of a streamed response is not generated unless
			// the content is fetched.
			// This ensures that the generator of SUT is actually executed.
			$response->streamedContent();
		}

		return $response;
	}

	/**
	 * Delete.
	 *
	 * @param string[]    $ids
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function delete(
		array $ids,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): void {
		$response = $this->testCase->postJson('/api/Album::delete', ['albumIDs' => $ids]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Get position data of photos below the designated album.
	 *
	 * @param string      $id
	 * @param bool        $includeSubAlbums
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function getPositionData(
		string $id,
		bool $includeSubAlbums,
		int $expectedStatusCode = 201,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Album::getPositionData', [
			'albumID' => $id,
			'includeSubAlbums' => $includeSubAlbums,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
