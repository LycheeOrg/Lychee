<?php

namespace Tests\Feature;

use AccessControl;
use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testRotate()
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);

		/*
		* Make a copy of the image because import deletes the file and we want to be
		* able to use the test on a local machine and not just in CI.
		*/
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile(
			'public/uploads/import/night.jpg',
			'night.jpg',
			'image/jpeg',
			null,
			true
		);

		$id = $photos_tests->upload($file);

		$photos_tests->get($id, 'true');

		$response = $photos_tests->get($id, 'true');
		/*
		* Check some Exif data
		*/
		$response->assertJson([
			'height' => '4480',
			'id' => $id,
			'filesize' => 21104156,
			'small_dim' => '540x360',
			'medium_dim' => '1620x1080',
			'width' => '6720',
		]);

		$editor_enabled_value = Configs::get_value('editor_enabled');
		Configs::set('editor_enabled', '0');
		$response = $this->post('/api/PhotoEditor::rotate', [
			'photoID' => $id,
			'direction' => 1,
		]);
		$response->assertStatus(200);
		$response->assertSee('false', false);

		Configs::set('editor_enabled', '1');
		$photos_tests->rotate('-1', 1, 'false');
		$photos_tests->rotate($id, 'asdq', 'false', 422);
		$photos_tests->rotate($id, '2', 'false');

		$response = $photos_tests->rotate($id, 1);
		/*
		* Check some Exif data
		*/
		$response->assertJson([
			'height' => '6720',
			'id' => $id,
			// 'filesize' => 21104156, // This changes during the image manipulation sadly.
			'small_dim' => '240x360',
			'medium_dim' => '720x1080',
			'width' => '4480',
		]);

		$photos_tests->rotate($id, -1);

		/*
		* Check some Exif data
		*/
		$response = $photos_tests->get($id, 'true');
		$response->assertJson([
			'height' => '4480',
			'id' => $id,
			// 'filesize' => 21104156, // This changes during the image manipulation sadly.
			'small_dim' => '540x360',
			'medium_dim' => '1620x1080',
			'width' => '6720',
		]);

		$photos_tests->delete($id, 'true');

		// reset
		Configs::set('editor_enabled', $editor_enabled_value);

		AccessControl::logout();
	}
}
