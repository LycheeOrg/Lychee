<?php

namespace Tests\Feature\Lib;

use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Photo\Archive;
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
	 * @param UploadedFile $file
	 *
	 * @return int the id of the photo
	 */
	public function upload(UploadedFile &$file): int
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
			[
				'albumID' => '0',
				'0' => $file,
			]
		);
		if ($response->getStatusCode() === 500) {
			$response->dump();
		}
		$response->assertSuccessful();
		$response->assertDontSee('Error');

		return $response->decodeResponseJson()->offsetGet('id');
	}

	/**
	 * Try uploading a picture without the file argument (will trigger the validate).
	 */
	protected function wrong_upload()
	{
		$response = $this->testCase->postJson(
			'/api/Photo::add',
			[
				'albumID' => '0',
			]
		);

		$response->assertStatus(422);
		$response->assertSee('The 0 field is required');
	}

	/**
	 * Try uploading a picture without the file type (will trigger the hasfile).
	 */
	protected function wrong_upload2()
	{
		$response = $this->testCase->postJson(
			'/api/Photo::add',
			[
				'albumID' => '0',
				'0' => '1',
			]
		);
		$response->assertStatus(422);
		$response->assertSee('The 0 must be a file');
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	) {
		$response = $this->testCase->json('POST', '/api/Photo::get', [
			'photoID' => $photo_id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}

	/**
	 * is photto ID visible in unsorted ?
	 *
	 * @param int $photoID
	 */
	protected function see_in_unsorted(int $photoID)
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertStatus(200);
		$response->assertSee(strval($photoID), false);
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	) {
		$response = $this->testCase->postJson(
			'/api/Photo::setDescription', [
				'description' => $description,
				'photoID' => $id,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
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
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	protected function set_public(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	) {
		$response = $this->testCase->postJson(
			'/api/Photo::setPublic', [
				'photoID' => $id,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Set license.
	 *
	 * @param string      $id
	 * @param string      $license
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	protected function set_license(
		string $id,
		string $license,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	) {
		$response = $this->testCase->postJson(
			'/api/Photo::setLicense', [
				'photoID' => $id,
				'license' => $license,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Set Album.
	 *
	 * @param string      $album_id
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	protected function set_album(
		string $album_id,
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	) {
		$response = $this->testCase->postJson(
			'/api/Photo::setAlbum', [
				'photoIDs' => $id,
				'albumID' => $album_id,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Duplicate a picture.
	 *
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	protected function duplicate(
		string $id,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/Photo::duplicate', [
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}

	/**
	 * We only test for a code 200.
	 *
	 * @param string $id
	 * @param string $kind
	 */
	protected function download(
		string $id,
		string $kind = Archive::FULL
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
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	protected function delete(
		string $id,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	) {
		$response = $this->testCase->json('POST', '/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
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
	 * @param string   $assertSee
	 *
	 * @return TestResponse
	 */
	protected function rotate(
		string $id,
		$direction,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	) {
		$response = $this->testCase->json('POST', '/api/PhotoEditor::rotate', [
			'photoID' => $id,
			'direction' => $direction,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response;
	}
}
