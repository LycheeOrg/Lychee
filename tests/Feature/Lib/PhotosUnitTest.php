<?php

namespace Tests\Feature\Lib;

use App\Actions\Photo\Archive;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PhotosUnitTest
{
	private TestCase $testCase;

	public function __construct(TestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * Try upload a picture.
	 *
	 * @param UploadedFile $file
	 * @param string       $albumID
	 *
	 * @return int the id of the photo
	 */
	public function upload(UploadedFile $file, string $albumID = '0'): int
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
			[
				'albumID' => $albumID,
				'0' => $file,
			]
		);

		$response->assertSuccessful();
		$response->assertDontSee('Error');

		return $response->offsetGet('id');
	}

	/**
	 * Try uploading a picture without the file argument (will trigger the validation).
	 */
	public function wrong_upload(): void
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
	 * Try uploading a picture which is not a file (will trigger the validation).
	 */
	public function wrong_upload2(): void
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
	 * @param string      $photo_id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function get(
		string $photo_id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
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
	 * is photo with given ID visible in unsorted?
	 *
	 * @param int $photoID
	 */
	public function see_in_unsorted(int $photoID): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertStatus(200);
		$response->assertSee(strval($photoID), false);
	}

	/**
	 * is photo with given ID NOT visible in unsorted?
	 *
	 * @param string $id
	 */
	public function dont_see_in_unsorted(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is photo with given ID visible in recent?
	 *
	 * @param string $id
	 */
	public function see_in_recent(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'recent',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is photo with given ID NOT visible in recent?
	 *
	 * @param string $id
	 */
	public function dont_see_in_recent(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'recent',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is photo with given ID visible in shared?
	 *
	 * @param string $id
	 */
	public function see_in_shared(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'public',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is photo with given ID NOT visible in shared?
	 *
	 * @param string $id
	 */
	public function dont_see_in_shared(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'public',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id, false);
	}

	/**
	 * is photo with given ID visible in favorite?
	 *
	 * @param string $id
	 */
	public function see_in_favorite(string $id): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'starred',
		]);
		$response->assertStatus(200);
		$response->assertSee($id, false);
	}

	/**
	 * is photo with given ID NOT visible in favorite ?
	 *
	 * @param string $id
	 */
	public function dont_see_in_favorite(string $id): void
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
	 * @param string      $id
	 * @param string      $title
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_title(
		string $id,
		string $title,
		int $expectedStatusCode = 200,
		?string $assertSee = 'true'
	): void {
		/**
		 * Try to set the title.
		 */
		$response = $this->testCase->json('POST', '/api/Photo::setTitle', [
			'title' => $title,
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Set Description.
	 *
	 * @param string      $id
	 * @param string      $description
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_description(
		string $id,
		string $description,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
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
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_star(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = 'true'
	): void {
		$response = $this->testCase->json('POST', '/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Set tags.
	 *
	 * @param string      $id
	 * @param string      $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_tag(
		string $id,
		string $tags,
		int $expectedStatusCode = 200,
		?string $assertSee = 'true'
	): void {
		$response = $this->testCase->json('POST', '/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => $tags,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}
	}

	/**
	 * Set public.
	 *
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_public(
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
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
	public function set_license(
		string $id,
		string $license,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
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
	public function set_album(
		string $album_id,
		string $id,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
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
	public function duplicate(
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
	public function download(
		string $id,
		string $kind = Archive::FULL
	): void {
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
	public function delete(
		string $id,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
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
	 * @param string      $path
	 * @param string      $delete_imported
	 * @param string      $album_id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return string
	 */
	public function import(
		string $path,
		string $delete_imported = '0',
		string $album_id = '0',
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): string {
		$response = $this->testCase->json('POST', '/api/Import::server', [
			'function' => 'Import::server',
			'albumID' => $album_id,
			'path' => $path,
			'delete_imported' => $delete_imported,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee);
		}

		return $response->streamedContent();
	}

	/**
	 * Rotate a picture.
	 *
	 * @param string      $id
	 * @param string      $direction
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function rotate(
		string $id,
		string $direction,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
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
