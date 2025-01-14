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

use App\Mail\PhotosAdded;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyUsers;

class NotificationTest extends AbstractTestCase
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;

	protected AlbumsUnitTest $albums_tests;
	protected UsersUnitTest $users_tests;
	protected PhotosUnitTest $photos_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->photos_tests = new PhotosUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testNotificationSetting(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('new_photos_notification');

		try {
			Auth::loginUsingId(1);

			$response = $this->postJson('/api/Settings::setNewPhotosNotification', [
				'new_photos_notification' => '1',
			]);
			$this->assertNoContent($response);
			static::assertEquals('1', Configs::getValue('new_photos_notification'));
		} finally {
			// set to initial
			Configs::set('new_photos_notification', $init_config_value);

			Auth::logout();
			Session::flush();
		}
	}

	public function testSetupUserEmail(): void
	{
		// add email to admin
		Auth::loginUsingId(1);
		$this->users_tests->update_email('test@gmail.com');

		Auth::logout();
		Session::flush();
	}

	public function testMailNotifications(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('new_photos_notification');

		try {
			Configs::set('new_photos_notification', '1');

			$photos = [
				'album123' => [
					'name' => 'Test Photo',
					'photos' => [
						'photo123' => [
							'thumb' => 'https://lychee.test.com/thumb.jpg',
							'link' => 'https://lychee.test.com',
						],
					],
				],
			];

			Mail::fake()->send(new PhotosAdded($photos));

			Mail::assertSent(PhotosAdded::class);
		} finally {
			Configs::set('new_photos_notification', $init_config_value);
		}
	}

	public function testSetAlbumForNotification(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('new_photos_notification');
		Configs::set('new_photos_notification', '1');

		Auth::loginUsingId(1);
		$albumID = $this->albums_tests->add(null, 'Album 1')->offsetGet('id');
		$photoID = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');

		$this->photos_tests->set_album($albumID, [$photoID]);

		Configs::set('new_photos_notification', $init_config_value);
	}
}
