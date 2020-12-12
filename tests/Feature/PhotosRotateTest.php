<?php

namespace Tests\Feature;

use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testRotate()
	{
		$photos_tests = new PhotosUnitTest();
		$session_tests = new SessionUnitTest();

		$session_tests->log_as_id(0);

		/*
		* Make a copy of the image because import deletes the file and we want to be
		* able to use the test on a local machine and not just in CI.
		*/
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile(
			'public/uploads/import/night.jpg',
			'night.jpg',
			'image/jpg',
			null,
			true
		);

		$id = $photos_tests->upload($this, $file);

		$photos_tests->get($this, $id, 'true');

		$response = $photos_tests->get($this, $id, 'true');
		/*
		* Check some Exif data
		*/
		$response->assertJson([
			'height' => '4480',
			'id' => $id,
			'size' => '20.1 MB',
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
		$photos_tests->rotate($this, '-1', 1, 'false');
		$photos_tests->rotate($this, $id, 'asdq', 'false', 422);
		$photos_tests->rotate($this, $id, '2', 'false');
		$photos_tests->rotate($this, $id, 1);

		/*
		* Check some Exif data
		*/
		$response = $photos_tests->get($this, $id, 'true');
		$response->assertJson([
			'height' => '6720',
			'id' => $id,
			// 'size' => '20.1 MB', // This changes during the image manipulation sadly.
			'small_dim' => '360x540',
			'medium_dim' => '1080x1620',
			'width' => '4480',
		]);

		$photos_tests->rotate($this, $id, -1);

		/*
		* Check some Exif data
		*/
		$response = $photos_tests->get($this, $id, 'true');
		$response->assertJson([
			'height' => '4480',
			'id' => $id,
			// 'size' => '20.1 MB', // This changes during the image manipulation sadly.
			'small_dim' => '540x360',
			'medium_dim' => '1620x1080',
			'width' => '6720',
		]);

		$photos_tests->delete($this, $id, 'true');

		// reset
		Configs::set('editor_enabled', $editor_enabled_value);

		$session_tests->logout($this);
	}
}
