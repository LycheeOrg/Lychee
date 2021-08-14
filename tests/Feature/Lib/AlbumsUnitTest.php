<?php

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AlbumsUnitTest
{
	private TestCase $testCase;

	public function __construct(TestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * Add an album.
	 *
	 * @param string      $parent_id
	 * @param string      $title
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function add(
		string $parent_id,
		string $title,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$params = [
			'title' => $title,
			'parent_id' => $parent_id,
		];

		$response = $this->testCase->json('POST', '/api/Album::add', $params);
		if ($response->getStatusCode() === 500) {
			$response->dump();
		}
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}

	/**
	 * Add an album.
	 *
	 * @param string      $title
	 * @param string      $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function addByTags(
		string $title,
		string $tags,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$params = [
			'title' => $title,
			'tags' => $tags,
		];

		$response = $this->testCase->json('POST', '/api/Album::addByTags', $params);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}

	/**
	 * Move albums.
	 *
	 * @param string      $ids
	 * @param string      $to
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function move(
		string $ids,
		string $to,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Album::move', [
			'albumIDs' => $to . ',' . $ids,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Get album by ID.
	 *
	 * @param string      $id
	 * @param string      $password
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function get(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json(
			'POST',
			'/api/Album::get',
			['albumID' => $id]
		);
		if ($response->getStatusCode() === 500) {
			$response->dump();
		}
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json(
			'POST',
			'/api/Album::unlock',
			['albumID' => $id, 'password' => $password]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Check if we see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 *
	 * @param string $id
	 */
	public function see_in_albums(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Albums::get', []);
		$response->assertOk();
		$response->assertSee($id, false);
	}

	/**
	 * Check if we don't see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 *
	 * @param string $id
	 */
	public function dont_see_in_albums(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Albums::get', []);
		$response->assertOk();
		$response->assertDontSee($id, false);
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
		?string $assertSee = null
	): void {
		$response = $this->testCase->json(
			'POST',
			'/api/Album::setTitle',
			['albumIDs' => $id, 'title' => $title]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
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
		?string $assertSee = null
	): void {
		$response = $this->testCase->json(
			'POST',
			'/api/Album::setDescription',
			['albumID' => $id, 'description' => $description]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
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
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Album::setLicense', [
			'albumID' => $id,
			'license' => $license,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
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
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Album::setSorting', [
			'albumID' => $id,
			'sortingCol' => $sortingCol,
			'sortingOrder' => $sortingOrder,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * @param string      $id
	 * @param int         $full_photo
	 * @param int         $public
	 * @param int         $visible
	 * @param int         $nsfw
	 * @param int         $downloadable
	 * @param int         $share_button_visible
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_public(
		string $id,
		int $full_photo = 1,
		int $public = 1,
		int $visible = 1,
		int $nsfw = 0,
		int $downloadable = 1,
		int $share_button_visible = 1,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Album::setPublic', [
			'full_photo' => $full_photo,
			'albumID' => $id,
			'public' => $public,
			'visible' => $visible,
			'nsfw' => $nsfw,
			'downloadable' => $downloadable,
			'share_button_visible' => $share_button_visible,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * @param string      $id
	 * @param string      $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_tags(
		string $id,
		string $tags,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Album::setShowTags', [
			'albumID' => $id,
			'show_tags' => $tags,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * We only test for a code 200.
	 *
	 * @param string $id
	 */
	public function download(string $id): void
	{
		$response = $this->testCase->call('GET', '/api/Album::getArchive', [
			'albumIDs' => $id,
		]);
		$response->assertStatus(200);
	}

	/**
	 * Delete.
	 *
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function delete(
		string $id,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson('/api/Album::delete', ['albumIDs' => $id]);
		if ($response->status() === 500) {
			$response->dump();
		}
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Test position data (Albums).
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function AlbumsGetPositionDataFull(
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/Albums::getPositionData', []);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}

	/**
	 * Test position data (Album).
	 *
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function AlbumGetPositionDataFull(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/Album::getPositionData', ['albumID' => $id, 'includeSubAlbums' => 'false']);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}
}
