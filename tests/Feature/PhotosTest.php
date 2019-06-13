<?php

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhotosTest extends TestCase
{
	/**
	 * Test photo operations.
	 *
	 * @return void
	 */
	public function testUpload()
	{
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		// Make a copy of the image because import deletes the file and we want to be
		// able to use the test on a local machine and not just in CI.
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile('public/uploads/import/night.jpg', 'night.jpg',
			'image/jpg', null, true);

		/**
		 * Test if we can upload.
		 */
		$response = $this->post('/api/Photo::add',
			[
				'albumID' => '0',
				'0' => $file,
			]);
		$id = $response->getContent();

		$response->assertStatus(200);
		$response->assertDontSee('Error');

		/**
		 * Check if we see the picture in unsorted.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => '0',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
		/**
		 * Check if we see the picture in recent.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'r',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
		/**
		 * Check if we see the picture in shared.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 's',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
		/**
		 * Check if we see the picture in favorites.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);

		/**
		 * Try to set the title.
		 */
		$response = $this->post('/api/Photo::setTitle', [
			'title' => "Night in Ploumanac'h",
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the description.
		 */
		$response = $this->post('/api/Photo::setDescription', [
			'description' => 'A night photography',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the stars.
		 */
		$response = $this->post('/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Check if we see the picture in favorite.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);

		/**
		 * Try to set the tags.
		 */
		$response = $this->post('/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => 'night',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::get', [
			'albumID' => '0',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($id);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'aperture' => 'f/2.8',
			'cameraDate' => '1',
			'description' => 'A night photography',
			'focal' => '16 mm',
			'height' => 4480,
			'id' => $id,
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'shutter' => '30 s',
			'size' => '20.1 MB',
			'small_dim' => '540x360',
			'medium_dim' => '1620x1080',
			'takedate' => '01 June 2019 at 01:28',
			'title' => "Night in Ploumanac'h",
			'type' => 'image/jpeg',
			'width' => 6720,
		]);

		/**
		 * Try to delete the picture.
		 */
		$response = $this->post('/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to get the picture again (should return an error).
		 */
		$response = $this->post('/api/Photo::get', [
			'albumID' => '0',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('false');
	}
}
