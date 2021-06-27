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

		$response = $photos_tests->get($id);
		/*
		* Check some Exif data
		*/
		$response->assertJson([
			'id' => $id,
			'filesize' => 21104156,
			'size_variants' => [
				'small' => [
					'width' => 540,
					'height' => 360,
				],
				'medium' => [
					'width' => 1620,
					'height' => 1080,
				],
				'original' => [
					'width' => 6720,
					'height' => 4480,
				],
			],
		]);

		$editor_enabled_value = Configs::get_value('editor_enabled');
		Configs::set('editor_enabled', '0');
		$response = $this->post('/api/PhotoEditor::rotate', [
			// somewhere in the Laravel middleware is a test which checks
			// if `photoID` is a string; find where
			'photoID' => (string) $id,
			'direction' => 1,
		]);
		$response->assertStatus(422);
		$response->assertSee('support for rotation disabled by configuration');

		Configs::set('editor_enabled', '1');
		$photos_tests->rotate('-1', 1, 404);
		$photos_tests->rotate($id, 'asdq', 422, 'The selected direction is invalid');
		$photos_tests->rotate($id, '2', 422, 'The selected direction is invalid');

		$response = $photos_tests->rotate($id, 1);
		/*
		* Check some Exif data
		*/
		$response->assertJson([
			'id' => $id,
			// 'filesize' => 21104156, // This changes during the image manipulation sadly.
			'size_variants' => [
				'small' => [
					'width' => 240,
					'height' => 360,
				],
				'medium' => [
					'width' => 720,
					'height' => 1080,
				],
				'original' => [
					'width' => 4480,
					'height' => 6720,
				],
			],
		]);

		$photos_tests->rotate($id, -1);

		/*
		* Check some Exif data
		*/
		$response = $photos_tests->get($id);
		$response->assertJson([
			'id' => $id,
			// 'filesize' => 21104156, // This changes during the image manipulation sadly.
			'size_variants' => [
				'small' => [
					'width' => 540,
					'height' => 360,
				],
				'medium' => [
					'width' => 1620,
					'height' => 1080,
				],
				'original' => [
					'width' => 6720,
					'height' => 4480,
				],
			],
		]);

		$photos_tests->delete($id);

		// reset
		Configs::set('editor_enabled', $editor_enabled_value);

		AccessControl::logout();
	}
}
