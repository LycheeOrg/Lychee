<?php

namespace Tests\Feature;

use AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
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
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

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

		$photos_tests->see_in_unsorted($id);
		$photos_tests->see_in_recent($id);
		$photos_tests->dont_see_in_shared($id);
		$photos_tests->dont_see_in_favorite($id);

		$photos_tests->set_title($id, "Night in Ploumanac'h");
		$photos_tests->set_description($id, 'A night photography');
		$photos_tests->set_star($id);
		$photos_tests->set_tag($id, 'night');
		$photos_tests->set_public($id);
		$photos_tests->set_license($id, 'WTFPL', '"Error: License not recognised!"');
		$photos_tests->set_license($id, 'CC0');
		$photos_tests->set_license($id, 'CC-BY-1.0');
		$photos_tests->set_license($id, 'CC-BY-2.0');
		$photos_tests->set_license($id, 'CC-BY-2.5');
		$photos_tests->set_license($id, 'CC-BY-3.0');
		$photos_tests->set_license($id, 'CC-BY-4.0');
		$photos_tests->set_license($id, 'CC-BY-ND-1.0');
		$photos_tests->set_license($id, 'CC-BY-ND-2.0');
		$photos_tests->set_license($id, 'CC-BY-ND-2.5');
		$photos_tests->set_license($id, 'CC-BY-ND-3.0');
		$photos_tests->set_license($id, 'CC-BY-ND-4.0');
		$photos_tests->set_license($id, 'CC-BY-SA-1.0');
		$photos_tests->set_license($id, 'CC-BY-SA-2.0');
		$photos_tests->set_license($id, 'CC-BY-SA-2.5');
		$photos_tests->set_license($id, 'CC-BY-SA-3.0');
		$photos_tests->set_license($id, 'CC-BY-SA-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-4.0');
		$photos_tests->set_license($id, 'reserved');

		$photos_tests->see_in_favorite($id);
		$photos_tests->see_in_shared($id);
		$response = $photos_tests->get($id, 'true');
		$photos_tests->download($id, 'FULL');

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'aperture' => 'f/2.8',
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

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		/*
		 * Erase tag
		 */
		$photos_tests->set_tag($id, '');

		/**
		 * We now test interaction with albums.
		 */
		$albumID = $albums_tests->add('0', 'test_album_2');
		$photos_tests->set_album('-1', $id, 'false');
		$photos_tests->set_album($albumID, $id, 'true');
		$albums_tests->download($albumID);
		$photos_tests->dont_see_in_unsorted($id);

		$photos_tests->duplicate($id, 'true');
		$response = $albums_tests->get($albumID, '', 'true');
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(2, count($array_content->photos));

		$ids = [];
		$ids[0] = $array_content->photos[0]->id;
		$ids[1] = $array_content->photos[1]->id;
		$photos_tests->delete($ids[0], 'true');
		$photos_tests->get($id[0], 'false');

		$photos_tests->dont_see_in_recent($ids[0]);
		$photos_tests->dont_see_in_unsorted($ids[1]);

		$albums_tests->set_public($albumID, 1, 1, 1, 0, 1, 1, 'true');

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		// delete the picture after displaying it
		$photos_tests->delete($ids[1], 'true');
		$photos_tests->get($id[1], 'false');
		$response = $albums_tests->get($albumID, '', 'true');
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(0, count($array_content->photos));

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

		$albums_tests->delete($albumID);

		$response = $this->get('/api/Photo::clearSymLink');
		$response->assertOk();
		$response->assertSee('true');

		AccessControl::logout();
	}

	public function testTrueNegative()
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);

		$photos_tests->wrong_upload($this);
		$photos_tests->wrong_upload2($this);
		$photos_tests->get('-1', 'false');
		$photos_tests->set_description('-1', 'test', 'false');
		$photos_tests->set_public('-1', 'false');
		$photos_tests->set_album('-1', '-1', 'false');
		$photos_tests->set_license('-1', 'CC0', 'false');

		AccessControl::logout();
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

	public function testImport()
	{
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		AccessControl::log_as_id(0);

		// save initial value
		$init_config_value = Configs::get_value('import_via_symlink');

		// enable import via symlink option
		Configs::set('import_via_symlink', '1');
		$this->assertEquals(Configs::get_value('import_via_symlink'), '1');

		$num_before_import = Photo::recent()->count();

		// upload the photo
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');
		$streamed_response = $photos_tests->import(base_path('public/uploads/import/'));

		// check if the file is still there (without symlinks the photo would have been deleted)
		$this->assertEquals(true, file_exists('public/uploads/import/night.jpg'));

		$response = $albums_tests->get('recent', '', 'true');
		$content = $response->getContent();
		$array_content = json_decode($content);
		$photos = new BaseCollection($array_content->photos);
		$this->assertEquals(Photo::recent()->count(), $photos->count());
		$ids = $photos->skip($num_before_import)->implode('id', ',');
		$photos_tests->delete($ids, 'true');

		$this->assertEquals($num_before_import, Photo::recent()->count());

		// set back to initial value
		Configs::set('import_via_symlink', $init_config_value);

		AccessControl::logout();
	}
}
