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
