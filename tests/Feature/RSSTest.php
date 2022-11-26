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

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\TestCase;

class RSSTest extends TestCase
{
	use RequiresEmptyPhotos;

	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);

		$this->setUpRequiresEmptyPhotos();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		parent::tearDown();
	}

	public function testRSS0(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('rss_enable');

		try {
			// set to 0
			Configs::set('rss_enable', '0');
			static::assertEquals('0', Configs::getValue('rss_enable'));

			// check redirection
			$response = $this->get('/feed');
			$response->assertStatus(412);
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	public function testRSS1(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('rss_enable');
		$init_full_photo = Configs::getValue('grants_full_photo_access');

		try {
			// set to 0
			Configs::set('rss_enable', '1');
			Configs::set('grants_full_photo_access', '0');
			static::assertEquals('1', Configs::getValue('rss_enable'));

			// check redirection
			$response = $this->get('/feed');
			$response->assertOk();

			// log as admin
			Auth::loginUsingId(0);

			// create an album
			$albumID = $this->albums_tests->add(null, 'test_album')->offsetGet('id');

			// upload a picture
			$photoID = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
			)->offsetGet('id');

			// set it to public
			$this->photos_tests->set_public($photoID, true);

			// try to get the RSS feed.
			$response = $this->get('/feed');
			$response->assertOk();

			// set picture to private
			$this->photos_tests->set_public($photoID, false);

			// move picture to album
			$this->photos_tests->set_album($albumID, [$photoID]);
			$this->albums_tests->set_protection_policy($albumID);

			// try to get the RSS feed.
			$response = $this->get('/feed');
			$response->assertOk();

			$this->albums_tests->delete([$albumID]);
		} finally {
			Configs::set('rss_enable', $init_config_value);
			Configs::set('grants_full_photo_access', $init_full_photo);

			Auth::logout();
			Session::flush();
		}
	}
}
