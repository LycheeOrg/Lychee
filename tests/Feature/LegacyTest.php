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

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature\Constants\TestConstants;
use Tests\Feature\LibUnitTests\AlbumsUnitTest;
use Tests\Feature\LibUnitTests\PhotosUnitTest;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;

class LegacyTest extends AbstractTestCase
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;

	protected AlbumsUnitTest $albums_tests;
	protected PhotosUnitTest $photos_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->photos_tests = new PhotosUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function testLegacyConversion(): void
	{
		Auth::loginUsingId(1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		)->offsetGet('id');

		/** @var \App\Models\Photo $photo */
		$photo = Photo::find($photoID);
		$legacyPhotoID = $photo->legacy_id;

		/** @var \App\Models\Album $album */
		$album = Album::find($albumID);
		$legacyAlbumID = $album->legacy_id;

		$response = $this->postJson('/api/Legacy::translateLegacyModelIDs', [
			'albumID' => $legacyAlbumID,
			'photoID' => $legacyPhotoID,
		]);
		$response->assertJson(['photoID' => $photoID, 'albumID' => $albumID]);

		Auth::logout();
		Session::flush();
	}
}

