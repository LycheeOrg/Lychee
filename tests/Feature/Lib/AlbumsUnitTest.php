<?php

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AlbumsUnitTest
{
	/**
	 * Add an album.
	 *
	 * @param TestCase $testCase
	 * @param string   $parent_id
	 * @param string   $title
	 * @param array    $tags
	 * @param string   $result
	 *
	 * @return string
	 */
	public function add(
		TestCase &$testCase,
		string $parent_id,
		string $title,
		string $result = 'true'
	) {
		$params = [
			'title' => $title,
			'parent_id' => $parent_id,
		];

		$response = $testCase->json('POST', '/api/Album::add', $params);
		$response->assertStatus(200);
		if ($result == 'true') {
			$response->assertDontSee('false');
		} else {
			$response->assertSee($result, false);
		}

		return $response->getContent();
	}

	/**
	 * Add an album.
	 *
	 * @param TestCase $testCase
	 * @param string   $parent_id
	 * @param string   $title
	 * @param array    $tags
	 * @param string   $result
	 *
	 * @return string
	 */
	public function addByTags(
		TestCase &$testCase,
		string $title,
		string $tags,
		string $result = 'true'
	) {
		$params = [
			'title' => $title,
			'tags' => $tags,
		];

		$response = $testCase->json('POST', '/api/Album::addByTags', $params);
		$response->assertStatus(200);
		if ($result == 'true') {
			$response->assertDontSee('false');
		} else {
			$response->assertSee($result, false);
		}

		return $response->getContent();
	}

	/**
	 * Move albums.
	 *
	 * @param TestCase $testCase
	 * @param string   $ids
	 * @param string   $result
	 *
	 * @return string
	 */
	public function move(
		TestCase &$testCase,
		string $ids,
		string $to,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::move', [
			'albumIDs' => $to . ',' . $ids,
		]);
		$response->assertStatus(200);
		if ($result == 'true') {
			$response->assertDontSee('false');
		} else {
			$response->assertSee($result, false);
		}

		return $response->getContent();
	}

	/**
	 * Get all albums.
	 *
	 * @param TestCase $testCase
	 * @param string   $result
	 *
	 * @return TestResponse
	 */
	public function get_all(
		TestCase &$testCase,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Albums::get', []);
		$response->assertOk();
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}

	/**
	 * Get album by ID.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $password
	 * @param string   $result
	 *
	 * @return TestResponse
	 */
	public function get(
		TestCase &$testCase,
		string $id,
		string $password = '',
		string $result = 'true'
	) {
		$response = $testCase->json(
			'POST',
			'/api/Album::get',
			['albumID' => $id, 'password' => $password]
		);
		$response->assertOk();
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}

	/**
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $password
	 * @param string   $result
	 */
	public function get_public(
		TestCase &$testCase,
		string $id,
		string $password = '',
		string $result = 'true'
	) {
		$response = $testCase->json(
			'POST',
			'/api/Album::getPublic',
			['albumID' => $id, 'password' => $password]
		);
		$response->assertOk();
		$response->assertSeeText($result);
	}

	/**
	 * Check if we see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function see_in_albums(TestCase &$testCase, string $id)
	{
		$response = $testCase->json('POST', '/api/Albums::get', []);
		$response->assertOk();
		$response->assertSee($id, false);
	}

	/**
	 * Check if we don't see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function dont_see_in_albums(TestCase &$testCase, string $id)
	{
		$response = $testCase->json('POST', '/api/Albums::get', []);
		$response->assertOk();
		$response->assertDontSee($id, false);
	}

	/**
	 * Change title.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $title
	 * @param string   $result
	 */
	public function set_title(
		TestCase &$testCase,
		string $id,
		string $title,
		string $result = 'true'
	) {
		$response = $testCase->json(
			'POST',
			'/api/Album::setTitle',
			['albumIDs' => $id, 'title' => $title]
		);
		$response->assertOk();
		$response->assertSee($result, false);
	}

	/**
	 * Change description.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $description
	 * @param string   $result
	 */
	public function set_description(
		TestCase &$testCase,
		string $id,
		string $description,
		string $result = 'true'
	) {
		$response = $testCase->json(
			'POST',
			'/api/Album::setDescription',
			['albumID' => $id, 'description' => $description]
		);
		$response->assertOk();
		$response->assertSee($result, false);
	}

	/**
	 * Set the licence.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $license
	 * @param string   $result
	 */
	public function set_license(
		TestCase &$testCase,
		string $id,
		string $license,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::setLicense', [
			'albumID' => $id,
			'license' => $license,
		]);
		$response->assertOk();
		$response->assertSee($result, false);
	}

	/**
	 * Set sorting.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $typePhotos
	 * @param string   $orderPhotos
	 * @param string   $result
	 */
	public function set_sorting(
		TestCase &$testCase,
		string $id,
		string $typePhotos,
		string $orderPhotos,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::setSorting', [
			'albumID' => $id,
			'typePhotos' => $typePhotos,
			'orderPhotos' => $orderPhotos,
		]);
		$response->assertOk();
		$response->assertSee($result, false);
	}

	/**
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param int      $full_photo
	 * @param int      $public
	 * @param int      $visible
	 * @param int      $downloadable
	 * @param int      $share_button_visible
	 * @param string   $result
	 */
	public function set_public(
		TestCase &$testCase,
		string $id,
		int $full_photo = 1,
		int $public = 1,
		int $visible = 1,
		int $downloadable = 1,
		int $share_button_visible = 1,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::setPublic', [
			'full_photo' => $full_photo,
			'albumID' => $id,
			'public' => $public,
			'visible' => $visible,
			'downloadable' => $downloadable,
			'share_button_visible' => $share_button_visible,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param array    $tags
	 * @param string   $result
	 */
	public function set_tags(
		TestCase &$testCase,
		string $id,
		string $tags,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::setShowTags', [
			'albumID' => $id,
			'show_tags' => $tags,
		]);

		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * We only test for a code 200.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $kind
	 */
	public function download(
		TestCase &$testCase,
		string $id,
		string $kind = 'FULL'
	) {
		$response = $testCase->call('GET', '/api/Album::getArchive', [
			'albumIDs' => $id,
		]);
		$response->assertStatus(200);
	}

	/**
	 * Delete.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	public function delete(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::delete', ['albumIDs' => $id]);
		$response->assertOk();
		$response->assertSee($result, false);
	}

	/**
	 * Test position data (Albums).
	 */
	public function AlbumsGetPositionDataFull(
		TestCase &$testCase,
		int $code = 200,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Albums::getPositionData', []);
		$response->assertStatus($code);
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}

	/**
	 * Test position data (Album).
	 */
	public function AlbumGetPositionDataFull(
		TestCase &$testCase,
		string $id,
		int $code = 200,
		string $result = 'true'
	) {
		$response = $testCase->json('POST', '/api/Album::getPositionData', ['albumID' => $id, 'includeSubAlbums' => 'false']);
		$response->assertStatus($code);
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}

	/**
	 * Test position data (Album).
	 */
	public function AlbumRebuildTakestamps(
		TestCase &$testCase,
		int $code = 200,
		string $result = 'true'
	) {
		$response = $testCase->get('/Albums/RebuildTakestamps');
		$response->assertStatus($code);
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}
}
