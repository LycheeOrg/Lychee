<?php

namespace Tests\Feature\Lib;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhotosUnitTest
{
	/**
	 * Try upload a picture.
	 *
	 * @param TestCase     $testcase
	 * @param UploadedFile $file
	 *
	 * @return string (id of the picture)
	 */
	public function upload(TestCase &$testcase, UploadedFile &$file)
	{
		$response = $testcase->post('/api/Photo::add',
			[
				'albumID' => '0',
				'0' => $file,
			]);
		$response->assertStatus(200);
		$response->assertDontSee('Error');

		return $response->getContent();
	}

	/**
	 * Try uploading a picture without the file argument (will trigger the validate).
	 *
	 * @param TestCase $testcase
	 */
	public function wrong_upload(TestCase &$testcase)
	{
		$response = $testcase->post('/api/Photo::add',
			[
				'albumID' => '0',
			]);
		$response->assertStatus(302);
	}

	/**
	 * Try uploading a picture without the file type (will trigger the hasfile).
	 *
	 * @param TestCase $testcase
	 */
	public function wrong_upload2(TestCase &$testcase)
	{
		$response = $testcase->post('/api/Photo::add',
			[
				'albumID' => '0',
				'0' => '1',
			]);
		$response->assertStatus(200);
		$response->assertSee('"Error: missing files"');
	}

	/**
	 * Get a photo given a photo id.
	 *
	 * @param TestCase $testCase
	 * @param string   $photo_id
	 * @param string   $result
	 *
	 * @return TestResponse
	 */
	public function get(
		TestCase &$testCase,
		string $photo_id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::get', [
			'photoID' => $photo_id,
		]);
		$response->assertStatus(200);
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	/**
	 * is ID visible in unsorted ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function see_in_unsorted(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => '0',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
	}

	/**
	 * is ID NOT visible in unsorted ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function dont_see_in_unsorted(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => '0',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
	}

	/**
	 * is ID visible in recent ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function see_in_recent(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 'r',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
	}

	/**
	 * is ID NOT visible in recent ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function dont_see_in_recent(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 'r',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
	}

	/**
	 * is ID visible in shared ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function see_in_shared(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 's',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
	}

	/**
	 * is ID NOT visible in shared ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function dont_see_in_shared(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 's',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
	}

	/**
	 * is ID visible in favorite ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function see_in_favorite(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
	}

	/**
	 * is ID NOT visible in favorite ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	public function dont_see_in_favorite(TestCase &$testCase, string $id)
	{
		$response = $testCase->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
	}

	/**
	 * Set Title.
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
		/**
		 * Try to set the title.
		 */
		$response = $testCase->post('/api/Photo::setTitle', [
			'title' => $title,
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set Description.
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
		/**
		 * Try to set the description.
		 */
		$response = $testCase->post('/api/Photo::setDescription', [
			'description' => $description,
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set Star.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	public function set_star(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set tags.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $tags
	 * @param string   $result
	 */
	public function set_tag(
		TestCase &$testCase,
		string $id,
		string $tags,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => $tags,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set public.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	public function set_public(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::setPublic', [
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set license.
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
		$response = $testCase->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => $license,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Set Album.
	 *
	 * @param TestCase $testCase
	 * @param string   $album_id
	 * @param string   $id
	 * @param string   $result
	 */
	public function set_album(
		TestCase &$testCase,
		string $album_id,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::setAlbum', [
			'photoIDs' => $id,
			'albumID' => $album_id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Duplicate a picture.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	public function duplicate(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Photo::duplicate', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
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
		$response = $testCase->call('GET', '/api/Photo::getArchive', [
			'photoIDs' => $id,
			'kind' => $kind,
		]);
		$response->assertStatus(200);
	}

	/**
	 * Delete a picture.
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
		$response = $testCase->post('/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}
}
