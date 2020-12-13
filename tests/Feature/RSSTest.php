<?php

namespace Tests\Feature;

use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
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
		$photos_tests = new PhotosUnitTest();
		$albums_tests = new AlbumsUnitTest();
		$session_tests = new SessionUnitTest();

		// log as admin
		$session_tests->log_as_id(0);

		// create an album
		$albumID = $albums_tests->add($this, '0', 'test_album', 'true');

		// upload a picture
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');
		$file = new UploadedFile(
			'public/uploads/import/night.jpg',
			'night.jpg',
			'image/jpg',
			null,
			true
		);
		$photoID = $photos_tests->upload($this, $file);

		// set it to public
		$photos_tests->set_public($this, $photoID);

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertStatus(200);

		// set picture to private
		$photos_tests->set_public($this, $photoID);

		// move picture to album
		$photos_tests->set_album($this, $albumID, $photoID, 'true');
		$albums_tests->set_public($this, $albumID, 1, 1, 1, 1, 1, 'true');

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertStatus(200);

		$albums_tests->delete($this, $albumID);

		Configs::set('Mod_Frame', $init_config_value);
		Configs::set('full_photo', $init_full_photo);
	}
}
