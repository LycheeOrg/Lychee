<?php

namespace Tests\Feature\Lib;

use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AlbumsUnitTest
{
	/**
	 * Add an album.
	 *
	 * @return string
	 */
	public function add(
		TestCase &$testCase,
		string $parent_id,
		string $title,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::add', [
			'title' => $title,
			'parent_id' => $parent_id,
		]);
		$response->assertStatus(200);
		if ($result == 'true') {
			$response->assertDontSee('false');
		} else {
			$response->assertSee($result);
		}

		return $response->getContent();
	}

	/**
	 * Get all albums.
	 *
	 * @return TestResponse
	 */
	public function get_all(
		TestCase &$testCase,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Albums::get', []);
		$response->assertOk();
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	/**
	 * Get album by ID.
	 *
	 * @return TestResponse
	 */
	public function get(
		TestCase &$testCase,
		string $id,
		string $password = '',
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::get',
			['albumID' => $id, 'password' => $password]);
		$response->assertOk();
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	public function get_public(
		TestCase &$testCase,
		string $id,
		string $password = '',
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::getPublic',
			['albumID' => $id, 'password' => $password]);
		$response->assertOk();
		$response->assertSeeText($result);
	}

	/**
	 * Check if we see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 */
	public function see_in_albums(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertSee($id);
	}

	/**
	 * Check if we don't see id in the list of all visible albums
	 * /!\ results varies depending if logged in or not !
	 */
	public function dont_see_in_albums(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertDontSee($id);
	}

	/**
	 * Change title.
	 */
	public function set_title(
		TestCase &$testCase,
		string $id,
		string $title,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::setTitle',
			['albumIDs' => $id, 'title' => $title]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * Change description.
	 */
	public function set_description(
		TestCase &$testCase,
		string $id,
		string $description,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::setDescription',
			['albumID' => $id, 'description' => $description]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * Set the licence.
	 */
	public function set_license(
		TestCase &$testCase,
		string $id,
		string $license,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::setLicense', [
			'albumID' => $id,
			'license' => $license,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	public function set_public(
		TestCase &$testCase,
		string $id,
		int $full_photo = 1,
		int $public = 1,
		int $visible = 1,
		int $downloadable = 1,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::setPublic', [
			'full_photo' => $full_photo,
			'albumID' => $id,
			'public' => $public,
			'visible' => $visible,
			'downloadable' => $downloadable,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * We only test for a code 200.
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
	 */
	public function delete(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Album::delete', ['albumIDs' => $id]);
		$response->assertOk();
		$response->assertSee($result);
	}
}