<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

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
		$response = $this->testCase->post(
			'/api/photo', [
				'albumID' => $albumID,
				'file' => $file,
			], [
				'CONTENT_TYPE' => 'multipart/form-data',
				'Accept' => 'application/json',
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
		$response = $this->testCase->post(
			'/api/photo',
			[
				'albumID' => null,
			], [
				'CONTENT_TYPE' => 'multipart/form-data',
				'Accept' => 'application/json',
			]
		);

		$response->assertUnprocessable();
		$response->assertSee('The file field is required');
	}

	/**
	 * Try uploading a picture which is not a file (will trigger the validation).
	 */
	public function wrong_upload2(): void
	{
		$response = $this->testCase->post(
			'/api/photo',
			[
				'albumID' => null,
				'file' => '1',
			], [
				'CONTENT_TYPE' => 'multipart/form-data',
				'Accept' => 'application/json',
			]
		);
		$response->assertUnprocessable();
		$response->assertSee('The file must be a file');
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
		$response = $this->testCase->getJson('/api/photo/' . $photo_id);
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
		$response = $this->testCase->getJson('/api/album/unsorted');
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
		$response = $this->testCase->getJson('/api/album/unsorted');
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
		$response = $this->testCase->getJson('/api/album/recent');
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
		$response = $this->testCase->getJson('/api/album/recent');
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
		$response = $this->testCase->getJson('/api/album/public');
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
		$response = $this->testCase->getJson('/api/album/public');
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
		$response = $this->testCase->getJson('/api/album/starred');
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
		$response = $this->testCase->getJson('/api/album/starred');
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		/**
		 * Try to set the title.
		 */
		$response = $this->testCase->patchJson('/api/photos/' . $id, [
			'title' => $title,
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson(
			'/api/photos/' . $id, [
				'description' => $description,
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Sets the "is-starred" property of the given photos.
	 *
	 * @param string[]    $ids
	 * @param bool        $isStarred
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_star(
		array $ids,
		bool $isStarred,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson('/api/photos/' . implode(',', $ids), [
			'is_starred' => $isStarred,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}
	}

	/**
	 * Set tags.
	 *
	 * @param string[]    $ids
	 * @param string[]    $tags
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_tag(
		array $ids,
		array $tags,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson('/api/photos/' . implode(',', $ids), [
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
	 * @param bool        $isPublic
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_public(
		string $id,
		bool $isPublic,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson(
			'/api/photos/' . $id, [
				'is_public' => $isPublic,
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
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson(
			'/api/photos/' . $id, [
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
	 * @param string[]    $ids
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_album(
		string $album_id,
		array $ids,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): void {
		$response = $this->testCase->patchJson(
			'/api/photos/' . implode(',', $ids), [
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
	 * @param string[]    $ids
	 * @param string|null $targetAlbumID
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function duplicate(
		array $ids,
		?string $targetAlbumID,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/photos/' . implode(',', $ids) . '/duplicate', [
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
			'/api/photos/' . $id . '/archive', ['kind' => $kind,
			], [
				'Accept' => '*/*',
			]
		);
		$response->assertOk();
	}

	/**
	 * Delete a picture.
	 *
	 * @param string[]    $ids
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function delete(
		array $ids,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->deleteJson('/api/photos/' . implode(',', $ids), []);
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
		$response = $this->testCase->postJson('/api/import/server', [
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
		$response = $this->testCase->postJson('/api/photo/' . $id . '/editor/rotate/' . $direction);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
