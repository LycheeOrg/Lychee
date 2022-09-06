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
use Symfony\Component\HttpFoundation\StreamedResponse;
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
	 * @return TestResponse
	 */
	public function upload(
		UploadedFile $file,
		?string $albumID = null,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->post(
			'/api/Photo::add', [
				'albumID' => $albumID,
				'file' => $file,
			], [
				'CONTENT_TYPE' => 'multipart/form-data',
				'Accept' => 'application/json',
			]
		);

		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Try uploading a picture without the file argument (will trigger the validation).
	 */
	public function wrong_upload(): void
	{
		$response = $this->testCase->post(
			'/api/Photo::add',
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
			'/api/Photo::add',
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
		$response = $this->testCase->postJson('/api/Photo::get', [
			'photoID' => $photo_id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
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
		$response = $this->testCase->postJson('/api/Photo::setTitle', [
			'title' => $title,
			'photoIDs' => [$id],
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson('/api/Photo::setStar', [
			'photoIDs' => $ids,
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson('/api/Photo::setTags', [
			'photoIDs' => $ids,
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
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson(
			'/api/Photo::setPublic', [
				'photoID' => $id,
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
	 * @param string[]    $ids
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 */
	public function set_album(
		string $album_id,
		array $ids,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): void {
		$response = $this->testCase->postJson(
			'/api/Photo::setAlbum', [
				'photoIDs' => $ids,
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
		$response = $this->testCase->postJson('/api/Photo::duplicate', [
			'photoIDs' => $ids,
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
	 * @param string[] $ids
	 * @param string   $kind
	 *
	 * @return TestResponse
	 */
	public function download(
		array $ids,
		string $kind = Archive::FULL,
		int $expectedStatusCode = 200
	): TestResponse {
		$response = $this->testCase->getWithParameters(
			'/api/Photo::getArchive', [
				'photoIDs' => implode(',', $ids),
				'kind' => $kind,
			], [
				'Accept' => '*/*',
			]
		);
		$response->assertStatus($expectedStatusCode);
		if ($response->baseResponse instanceof StreamedResponse) {
			// The content of a streamed response is not generated unless
			// the content is fetched.
			// This ensures that the generator of SUT is actually executed.
			$response->streamedContent();
		}

		return $response;
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
		$response = $this->testCase->postJson('/api/Photo::delete', [
			'photoIDs' => $ids,
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
	 * @param string|null $album_id
	 * @param bool|null   $delete_imported    tri-state, `null` means let the server pick the configured default
	 * @param bool|null   $skip_duplicates    tri-state, `null` means let the server pick the configured default
	 * @param bool|null   $import_via_symlink tri-state, `null` means let the server pick the configured default
	 * @param bool|null   $resync_metadata    tri-state, `null` means let the server pick the configured default
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return string the streamed progress report
	 */
	public function importFromServer(
		string $path,
		?string $album_id = null,
		?bool $delete_imported = null,
		?bool $skip_duplicates = null,
		?bool $import_via_symlink = null,
		?bool $resync_metadata = null,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): string {
		$requestParams = [
			'albumID' => $album_id,
			'paths' => [$path],
		];

		if ($delete_imported !== null) {
			$requestParams['delete_imported'] = $delete_imported;
		}

		if ($skip_duplicates !== null) {
			$requestParams['skip_duplicates'] = $skip_duplicates;
		}

		if ($import_via_symlink !== null) {
			$requestParams['import_via_symlink'] = $import_via_symlink;
		}

		if ($resync_metadata !== null) {
			$requestParams['resync_metadata'] = $resync_metadata;
		}

		$response = $this->testCase->postJson(
			'/api/Import::server',
			$requestParams
		);

		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response->streamedContent();
	}

	/**
	 * Imports a photo from a remote URL.
	 *
	 * @param string[]    $urls               URLs to import photos from
	 * @param string|null $album_id           ID of album to import into
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function importFromUrl(
		array $urls,
		?string $album_id = null,
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson(
			'/api/Import::url', [
				'albumID' => $album_id,
				'urls' => $urls,
			]);

		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
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
