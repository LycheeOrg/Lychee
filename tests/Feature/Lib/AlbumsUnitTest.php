<?php

namespace Tests\Feature\Lib;

use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AlbumsUnitTest
{
	/**
	 * Add an album.
	 *
	 * @param TestCase $testCase
	 * @param string   $parent_id
	 * @param string   $title
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
		$response = $testCase->post('/api/Album::get',
			['albumID' => $id, 'password' => $password]);
		$response->assertOk();
		if ($result != 'true') {
			$response->assertSee($result);
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
		$response = $testCase->post('/api/Album::getPublic',
			['albumID' => $id, 'password' => $password]);
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
		$response = $testCase->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertSee($id);
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
		$response = $testCase->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertDontSee($id);
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
		$response = $testCase->post('/api/Album::setTitle',
			['albumIDs' => $id, 'title' => $title]);
		$response->assertOk();
		$response->assertSee($result);
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
		$response = $testCase->post('/api/Album::setDescription',
			['albumID' => $id, 'description' => $description]);
		$response->assertOk();
		$response->assertSee($result);
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
		$response = $testCase->post('/api/Album::setLicense',
			['albumID' => $id, 'license' => $license]);
		$response->assertOk();
		$response->assertSee($result);
	}
}