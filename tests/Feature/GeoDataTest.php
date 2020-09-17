<?php

namespace Tests\Feature;

use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class GeoDataTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testGeo()
	{
		$photos_tests = new PhotosUnitTest();
		$albums_tests = new AlbumsUnitTest();
		$session_tests = new SessionUnitTest();

		$session_tests->log_as_id(0);

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

		$id = $photos_tests->upload($this, $file);

		$response = $photos_tests->get($this, $id, 'true');
		$photos_tests->see_in_unsorted($this, $id);
		/*
		* Check some Exif data
		*/
		$response->assertJson(
			[
				'id' => $id,
				'title' => 'mongolia',
				'width' => '1280',
				'height' => '850',
				'type' => 'image/jpeg',
				'size' => '196.6 KB',
				'iso' => '200',
				'aperture' => 'f/13.0',
				'make' => 'NIKON CORPORATION',
				'model' => 'NIKON D5000',
				'shutter' => '1/640 s',
				'focal' => '44 mm',
				'altitude' => '1633.0000',
				'license' => 'none',
				'medium' => '',
				'medium_dim' => '',
				'medium2x' => '',
				'medium2x_dim' => '',
				'small_dim' => '542x360',
				'takedate' => '17 August 2011 at 16:39',
				'public' => '0',
				'downloadable' => '1',
				'share_button_visible' => '1',
			]
		);

		$albumID = $albums_tests->add($this, '0', 'test_mongolia');
		$photos_tests->set_album($this, $albumID, $id, 'true');
		$photos_tests->dont_see_in_unsorted($this, $id);
		$response = $albums_tests->get($this, $albumID, '', 'true');
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
		$albums_tests->AlbumsGetPositionDataFull($this, 200); // we need to fix this

		// set to 1
		Configs::set('map_display', '1');
		$this->assertEquals(Configs::get_value('map_display'), '1');
		$response = $albums_tests->AlbumsGetPositionDataFull($this, 200);
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(1, count($array_content->photos));
		$this->assertEquals($id, $array_content->photos[0]->id);

		// set to 0
		Configs::set('map_display', '0');
		$this->assertEquals(Configs::get_value('map_display'), '0');
		$albums_tests->AlbumGetPositionDataFull($this, $albumID, 200); // we need to fix this

		// set to 1
		Configs::set('map_display', '1');
		$this->assertEquals(Configs::get_value('map_display'), '1');
		$response = $albums_tests->AlbumGetPositionDataFull($this, $albumID, 200);
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(1, count($array_content->photos));
		$this->assertEquals($id, $array_content->photos[0]->id);

		$photos_tests->delete($this, $id, 'true');
		$albums_tests->delete($this, $albumID);

		// reset
		Configs::set('map_display', $map_display_value);

		$session_tests->logout($this);
	}
}
