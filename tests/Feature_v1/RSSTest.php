<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v1;

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Traits\RequiresEmptyPhotos;

class RSSTest extends AbstractTestCase
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
			$this->assertStatus($response, 412);
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
			$this->assertOk($response);

			// log as admin
			Auth::loginUsingId(1);

			// create an album
			$albumID = $this->albums_tests->add(null, 'test_album')->offsetGet('id');

			// upload a picture
			$photoID = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
			)->offsetGet('id');

			// try to get the RSS feed.
			$response = $this->get('/feed');
			$this->assertOk($response);

			// move picture to album
			$this->photos_tests->set_album($albumID, [$photoID]);
			$this->albums_tests->set_protection_policy($albumID);

			// try to get the RSS feed.
			$response = $this->get('/feed');
			$this->assertOk($response);

			$this->albums_tests->delete([$albumID]);
		} finally {
			Configs::set('rss_enable', $init_config_value);
			Configs::set('grants_full_photo_access', $init_full_photo);

			Auth::logout();
			Session::flush();
		}
	}
}
