<?php

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\PhotosUnitTest;

class VideoTest extends \Tests\TestCase
{
	/**
	 * Tests a trick video which is falsely identified as `application/octet-stream`.
	 *
	 * @return void
	 */
	public function testUpload()
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);
		$init_config_value = Configs::get_value('has_exiftool');
		Configs::set('has_exiftool', '2');

		if (Configs::hasExiftool()) {
			/*
			 * Make a copy of the image because import deletes the file, and we want to be
			 * able to use the test on a local machine and not just in CI.
			 * We must use a temporary file name without/with a wrong file
			 * extension as a real upload would do in order to trigger the
			 * problematic code path.
			 */
			$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
			copy('tests/Samples/gaming.mp4', $tmpFilename);

			$file = new UploadedFile(
				$tmpFilename,
				'gaming.mp4',
				'video/mp4',
				null,
				true
			);

			$id = $photos_tests->upload($file);
			$response = $photos_tests->get($id);
			$response->assertOk();
			$response->assertJson([
				'album_id' => null,
				'id' => $id,
				'title' => 'gaming',
				'type' => 'video/mp4',
				'size_variants' => [
					'thumb' => [
						'width' => 200,
						'height' => 200,
					],
					'thumb2x' => [
						'width' => 400,
						'height' => 400,
					],
					'small' => [
						'width' => 640,
						'height' => 360,
					],
					'small2x' => [
						'width' => 1280,
						'height' => 720,
					],
					'original' => [
						'width' => 1920,
						'height' => 1080,
						'filesize' => 66781184,
					],
				],
			]);
		} else {
			$this->markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		Configs::set('has_exiftool', $init_config_value);
		AccessControl::logout();
	}
}