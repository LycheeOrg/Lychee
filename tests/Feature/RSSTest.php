<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class RSSTest extends TestCase
{
	public function testRSS0(): void
	{
		// save initial value
		$init_config_value = Configs::get_value('rss_enable');

		// set to 0
		Configs::set('rss_enable', '0');
		static::assertEquals('0', Configs::get_value('rss_enable'));

		// check redirection
		$response = $this->get('/feed');
		$response->assertStatus(412);

		Configs::set('Mod_Frame', $init_config_value);
	}

	public function testRSS1(): void
	{
		// save initial value
		$init_config_value = Configs::get_value('rss_enable');
		$init_full_photo = Configs::get_value('full_photo');

		// set to 0
		Configs::set('rss_enable', '1');
		Configs::set('full_photo', '0');
		static::assertEquals('1', Configs::get_value('rss_enable'));

		// check redirection
		$response = $this->get('/feed');
		$response->assertOk();

		// now we start adding some stuff
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		// log as admin
		AccessControl::log_as_id(0);

		// create an album
		$albumID = $albums_tests->add(null, 'test_album')->offsetGet('id');

		// upload a picture
		$photoID = $photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		// set it to public
		$photos_tests->set_public($photoID, true);

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertOk();

		// set picture to private
		$photos_tests->set_public($photoID, false);

		// move picture to album
		$photos_tests->set_album($albumID, [$photoID]);
		$albums_tests->set_protection_policy($albumID);

		// try to get the RSS feed.
		$response = $this->get('/feed');
		$response->assertOk();

		$albums_tests->delete([$albumID]);

		Configs::set('Mod_Frame', $init_config_value);
		Configs::set('full_photo', $init_full_photo);

		AccessControl::logout();
	}
}
