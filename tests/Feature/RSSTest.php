<?php

namespace Tests\Feature;

use AccessControl;
use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class RSSTest extends TestCase
{
	public function testRSS0()
	{
		// save initial value
		$init_config_value = Configs::get_value('rss_enable');

		// set to 0
		Configs::set('rss_enable', '0');
		$this->assertEquals(Configs::get_value('rss_enable'), '0');

		// check redirection
		$response = $this->get('/feed');
		$response->assertStatus(404);

		Configs::set('Mod_Frame', $init_config_value);
	}

	public function testRSS1()
	{
		// save initial value
		$init_config_value = Configs::get_value('rss_enable');
		$init_full_photo = Configs::get_value('full_photo');

		// set to 0
		Configs::set('rss_enable', '1');
		Configs::set('full_photo', '0');
		$this->assertEquals(Configs::get_value('rss_enable'), '1');

		// check redirection
		$response = $this->get('/feed');
		$response->assertStatus(200);

		// now we start adding some stuff
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		// log as admin
		AccessControl::log_as_id(0);

		// create an album
		$albumID = $albums_tests->add('0', 'test_album', 'true');

		// upload a picture
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');
		$file = new UploadedFile(
			'public/uploads/import/night.jpg',
			'night.jpg',
			'image/jpeg',
			null,
			true
		);
		$photoID = $photos_tests->upload($file);

		// set it to public
		$photos_tests->set_public($photoID);

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertStatus(200);

		// set picture to private
		$photos_tests->set_public($photoID);

		// move picture to album
		$photos_tests->set_album($albumID, $photoID);
		$albums_tests->set_public($albumID, 1, 1, 1, 0, 1, 1, 'true');

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertStatus(200);

		$albums_tests->delete($albumID);

		Configs::set('Mod_Frame', $init_config_value);
		Configs::set('full_photo', $init_full_photo);

		AccessControl::logout();
	}
}
