<?php

namespace Tests\Feature;

use AccessControl;
use App\Models\Configs;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class GeoDataTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testGeo()
	{
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		AccessControl::log_as_id(0);

		/*
		* Make a copy of the image because import deletes the file and we want to be
		* able to use the test on a local machine and not just in CI.
		*/
		copy('tests/Feature/mongolia.jpeg', 'public/uploads/import/mongolia.jpeg');

		$file = new UploadedFile(
			'public/uploads/import/mongolia.jpeg',
			'mongolia.jpeg',
			'image/jpeg',
			null,
			true
		);

		$id = $photos_tests->upload($file);

		$response = $photos_tests->get($id);
		$photos_tests->see_in_unsorted($id);
		/*
		 * Check some Exif data
		 * The metadata extractor is unable to extract an explicit timezone
		 * for the test file.
		 * Hence, the attribute `taken_at` is relative to the default timezone
		 * of the application.
		 * Actually, the `exiftool` reports an attribute `Time Zone: +08:00`,
		 * if the tool is invoked from the command line, but the PHP wrapper
		 * \PHPExif\Exif does not use it.
		 */
		$taken_at = Carbon::create(
			2011, 8, 17, 16, 39, 37
		);
		$response->assertJson(
			[
				'id' => $id,
				'title' => 'mongolia',
				'type' => 'image/jpeg',
				'filesize' => 201316,
				'iso' => '200',
				'aperture' => 'f/13.0',
				'make' => 'NIKON CORPORATION',
				'model' => 'NIKON D5000',
				'shutter' => '1/640 s',
				'focal' => '44 mm',
				'altitude' => '1633.0000',
				'license' => 'none',
				'taken_at' => $taken_at->format(\DateTimeInterface::ATOM),
				'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
				'public' => 0,
				'downloadable' => true,
				'share_button_visible' => true,
				'size_variants' => [
					'thumb' => [
						'width' => 200,
						'height' => 200,
					],
					'small' => [
						'width' => 542,
						'height' => 360,
					],
					'medium' => null,
					'medium2x' => null,
					'original' => [
						'width' => 1280,
						'height' => 850,
					],
				],
			]
		);

		$albumID = $albums_tests->add('0', 'test_mongolia');
		$photos_tests->set_album($albumID, $id);
		$photos_tests->dont_see_in_unsorted($id);
		$response = $albums_tests->get($albumID, '', 'true');
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(1, count($array_content->photos));
		$this->assertEquals($id, $array_content->photos[0]->id);

		// now we test position Data
		// save initial value
		$map_display_value = Configs::get_value('map_display');

		// set to 0
		Configs::set('map_display', '0');
		$this->assertEquals(Configs::get_value('map_display'), '0');
		$albums_tests->AlbumsGetPositionDataFull(200); // we need to fix this

		// set to 1
		Configs::set('map_display', '1');
		$this->assertEquals(Configs::get_value('map_display'), '1');
		$response = $albums_tests->AlbumsGetPositionDataFull(200);
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(1, count($array_content->photos));
		$this->assertEquals($id, $array_content->photos[0]->id);

		// set to 0
		Configs::set('map_display', '0');
		$this->assertEquals(Configs::get_value('map_display'), '0');
		$albums_tests->AlbumGetPositionDataFull($albumID, 200); // we need to fix this

		// set to 1
		Configs::set('map_display', '1');
		$this->assertEquals(Configs::get_value('map_display'), '1');
		$response = $albums_tests->AlbumGetPositionDataFull($albumID, 200);
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(1, count($array_content->photos));
		$this->assertEquals($id, $array_content->photos[0]->id);

		$photos_tests->delete($id);
		$albums_tests->delete($albumID);

		// reset
		Configs::set('map_display', $map_display_value);

		AccessControl::logout();
	}
}
