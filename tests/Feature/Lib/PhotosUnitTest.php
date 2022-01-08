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
	 * @param string|null  $albumID
	 *
	 * @return string the id of the photo
	 */
	public function upload(UploadedFile $file, ?string $albumID = null): string
	{
		$response = $this->testCase->postJson(
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
				'albumID' => null,
			]
		);

		$response->assertUnprocessable();
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
				'albumID' => null,
				'0' => '1',
			]
		);
		$response->assertUnprocessable();
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
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * is photo with given ID visible in unsorted?
	 *
	 * @param string $photoID
	 */
	public function see_in_unsorted(string $photoID): void
	{
		$response = $this->testCase->json('POST', '/api/Album::get', [
			'albumID' => 'unsorted',
		]);
		$response->assertOk();
		$response->assertSee($photoID, false);
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
		$response->assertOk();
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
		$response->assertOk();
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
		$response->assertOk();
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
		$response->assertOk();
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
		$response->assertOk();
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
		$response->assertOk();
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
		$response->assertOk();
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
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
			$response->assertSee($assertSee, false);
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
		int $expectedStatusCode = 204,
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
			$response->assertSee($assertSee, false);
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->json('POST', '/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => $tags,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson(
			'/api/Photo::setPublic', [
				'photoID' => $id,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
			$response->assertSee($assertSee, false);
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
		int $expectedStatusCode = 204,
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
			$response->assertSee($assertSee, false);
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
		?string $targetAlbumID,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/Photo::duplicate', [
			'photoIDs' => $id,
			'albumID' => $targetAlbumID,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
		$response = $this->testCase->getWithParameters(
			'/api/Photo::getArchive', [
				'photoIDs' => [$id],
				'kind' => $kind,
			]
		);
		$response->assertOk();
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
		$response = $this->testCase->postJson('/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Import a picture.
	 *
	 * @param string      $path
	 * @param bool        $delete_imported
	 * @param string|null $album_id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return string
	 */
	public function import(
		string $path,
		bool $delete_imported = false,
		?string $album_id = null,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): string {
		$response = $this->testCase->postJson('/api/Import::server', [
			'function' => 'Import::server',
			'albumID' => $album_id,
			'path' => $path,
			'delete_imported' => $delete_imported,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
		$response = $this->testCase->postJson('/api/PhotoEditor::rotate', [
			'photoID' => $id,
			'direction' => $direction,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
