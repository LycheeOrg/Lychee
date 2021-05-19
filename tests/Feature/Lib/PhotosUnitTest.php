<?php

namespace Tests\Feature\Lib;

use App\Actions\Albums\Extensions\PublicIds;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PhotosUnitTest
{
	private $testCase = null;

	public function __construct(TestCase &$testCase)
	{
		$this->testCase = $testCase;
	}

	// This is called before any subsequent function call.
	// We use it to "refresh" the PublicIds as it is a singleton,
	// it stays the same through all the test once initialized.
	// This is not the desired behaviour as it is re-initialized per connection.
	public function __call($method, $arguments)
	{
		if (method_exists($this, $method)) {
			resolve(PublicIds::class)->refresh();
			// fwrite(STDERR, print_r(__CLASS__ . '\\' . $method, TRUE) . "\n");
			return call_user_func_array([$this, $method], $arguments);
		}
	}

	/**
	 * Try upload a picture.
	 *
	 * @param TestCase     $testcase
	 * @param UploadedFile $file
	 *
	 * @return string (id of the picture)
	 */
	public function upload(UploadedFile &$file)
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
			[
				'albumID' => '0',
				'0' => $file,
			]
		);
		if ($response->getStatusCode() !== 200) {
			$response->dump();
		}
		$response->assertStatus(200);
		$response->assertDontSee('Error');

		return $response->getContent();
	}

	/**
	 * Try uploading a picture without the file argument (will trigger the validate).
	 *
	 * @param TestCase $testcase
	 */
	protected function wrong_upload()
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
			[
				'albumID' => '0',
			]
		);
		$response->assertStatus(200);
		$response->assertSee('"Error: validation failed"', false);
	}

	/**
	 * Try uploading a picture without the file type (will trigger the hasfile).
	 *
	 * @param TestCase $testcase
	 */
	protected function wrong_upload2()
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
			[
				'albumID' => '0',
				'0' => '1',
			]
		);
		$response->assertStatus(200);
		$response->assertSee('"Error: missing files"', false);
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
	protected function get(
		string $photo_id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::get', [
			'photoID' => $photo_id,
		]);
		$response->assertStatus(200);
		if ($result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}

	/**
	 * is ID visible in unsorted ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function see_in_unsorted(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is ID NOT visible in unsorted ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function dont_see_in_unsorted(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is ID visible in recent ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function see_in_recent(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'recent',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is ID NOT visible in recent ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function dont_see_in_recent(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'recent',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is ID visible in shared ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function see_in_shared(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'public',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is ID NOT visible in shared ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function dont_see_in_shared(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'public',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is ID visible in favorite ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function see_in_favorite(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'starred',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is ID NOT visible in favorite ?
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 */
	protected function dont_see_in_favorite(string $id)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'starred',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * Set Title.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $title
	 * @param string   $result
	 */
	protected function set_title(
		string $id,
		string $title,
		string $result = 'true'
	) {
		/**
		 * Try to set the title.
		 */
		$response = $this->testCase->json('POST', '/api/Photo::setTitle', [
			'title' => $title,
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set Description.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $description
	 * @param string   $result
	 */
	protected function set_description(
		string $id,
		string $description,
		string $result = 'true'
	) {
		/**
		 * Try to set the description.
		 */
		$response = $this->testCase->json('POST', '/api/Photo::setDescription', [
			'description' => $description,
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set Star.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	protected function set_star(
		string $id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set tags.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $tags
	 * @param string   $result
	 */
	protected function set_tag(
		string $id,
		string $tags,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => $tags,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set public.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	protected function set_public(
		string $id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::setPublic', [
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set license.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $license
	 * @param string   $result
	 */
	protected function set_license(
		string $id,
		string $license,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => $license,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Set Album.
	 *
	 * @param TestCase $testCase
	 * @param string   $album_id
	 * @param string   $id
	 * @param string   $result
	 */
	protected function set_album(
		string $album_id,
		string $id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::setAlbum', [
			'photoIDs' => $id,
			'albumID' => $album_id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Duplicate a picture.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
	 */
	protected function duplicate(
		string $id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::duplicate', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * We only test for a code 200.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $kind
	 */
	protected function download(
		string $id,
		string $kind = 'FULL'
	) {
		$response = $this->testCase->call('GET', '/api/Photo::getArchive', [
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
	protected function delete(
		string $id,
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		if ($response->getStatusCode() !== 200) {
			$response->dump();
		}
		$response->assertStatus(200);
		$response->assertSee($result, false);
	}

	/**
	 * Import a picture.
	 *
	 * @param TestCase $testCase
	 * @param string   $path
	 * @param string   $delete_imported
	 * @param string   $album_id
	 * @param string   $result
	 *
	 * @return string
	 */
	protected function import(
		string $path,
		string $delete_imported = '0',
		string $album_id = '0',
		string $result = 'true'
	) {
		$response = $this->testCase->json('POST', '/api/Import::server', [
			'function' => 'Import::server',
			'albumID' => $album_id,
			'path' => $path,
			'delete_imported' => $delete_imported,
		]);
		$response->assertStatus(200);
		$response->assertSee('');

		return $response->streamedContent();
	}

	/**
	 * Rotate a picture.
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param          $direction
	 * @param string   $result
	 *
	 * @return TestResponse
	 */
	protected function rotate(
		string $id,
		$direction,
		string $result = 'true',
		int $code = 200
	) {
		$response = $this->testCase->json('POST', '/api/PhotoEditor::rotate', [
			'photoID' => $id,
			'direction' => $direction,
		]);
		$response->assertStatus($code);
		if ($code == 200 && $result != 'true') {
			$response->assertSee($result, false);
		}

		return $response;
	}
}
