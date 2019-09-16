<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
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

		$photos_tests = new PhotosUnitTest();
		$album_tests = new AlbumsUnitTest();

		// Make a copy of the image because import deletes the file and we want to be
		// able to use the test on a local machine and not just in CI.
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile('public/uploads/import/night.jpg', 'night.jpg',
			'image/jpg', null, true);

		$id = $photos_tests->upload($this, $file);
		$photos_tests->get($this, $id, 'true');

		$photos_tests->see_in_unsorted($this, $id);
		$photos_tests->see_in_recent($this, $id);
		$photos_tests->dont_see_in_shared($this, $id);
		$photos_tests->dont_see_in_favorite($this, $id);

		$photos_tests->set_title($this, $id, "Night in Ploumanac'h");
		$photos_tests->set_description($this, $id, 'A night photography');

		$photos_tests->set_star($this, $id);
		$photos_tests->see_in_favorite($this, $id);

		$photos_tests->set_tag($this, $id, 'night');

		$photos_tests->set_public($this, $id);
		$photos_tests->see_in_shared($this, $id);
		$photos_tests->set_license($this, $id, 'reserved');

		$response = $photos_tests->get($this, $id, 'true');
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
			'license' => 'reserved',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'public' => '1',
			'shutter' => '30 s',
			'size' => '20.1 MB',
			'small_dim' => '540x360',
			'star' => '1',
			'tags' => 'night',
			'medium_dim' => '1620x1080',
			'takedate' => '01 June 2019 at 01:28',
			'title' => "Night in Ploumanac'h",
			'type' => 'image/jpeg',
			'width' => 6720,
		]);

		$photos_tests->set_license($this, $id, 'CC0');
		$photos_tests->set_license($this, $id, 'CC-BY');
		$photos_tests->set_license($this, $id, 'CC-BY-ND');
		$photos_tests->set_license($this, $id, 'CC-BY-SA');
		$photos_tests->set_license($this, $id, 'CC-BY-NC');
		$photos_tests->set_license($this, $id, 'CC-BY-NC-ND');
		$photos_tests->set_license($this, $id, 'CC-BY-NC-SA');

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		$photos_tests->set_tag($this, $id, '');

		$albumID = $album_tests->add($this, '0', 'test_album_2');

		$photos_tests->set_album($this, $albumID, $id, 'true');
		$photos_tests->duplicate($this, $id, 'true');

		$response = $album_tests->get($this, $albumID, '', 'true');
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(2, count($array_content->photos));

		$photos_tests->delete($this, $id, 'true');
		$photos_tests->get($this, $id, 'false');

		$response = $this->post('/api/Album::setPublic', [
			'full_photo' => 1,
			'albumID' => $albumID,
			'public' => 1,
			'visible' => 1,
			'downloadable' => 1,
		]);
		$response->assertOk();

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		$this->assertEquals(Configs::get_value('gen_demo_js'), '1');

		// check redirection
		$response = $this->get('/demo');
		$response->assertStatus(200);
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);

		$response = $this->post('/api/Album::delete', ['albumIDs' => $albumID]);
		$response->assertOk();
		$response->assertSee('true');

		$response = $this->get('/api/Photo::clearSymLink');
		$response->assertOk();
		$response->assertSee('true');
	}

	public function testUpload2()
	{
		// save initial value
		$init_config_value1 = Configs::get_value('SL_enable');
		$init_config_value2 = Configs::get_value('SL_for_admin');

		// set to 0
		Configs::set('SL_enable', '1');
		Configs::set('SL_for_admin', '1');
		$this->assertEquals(Configs::get_value('SL_enable'), '1');
		$this->assertEquals(Configs::get_value('SL_for_admin'), '1');

		// just redo the test above :'D
		$this->testUpload();

		// set back to initial value
		Configs::set('SL_enable', $init_config_value1);
		Configs::set('SL_for_admin', $init_config_value2);
	}
}
