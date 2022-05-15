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
use App\Mail\PhotosAdded;
use App\Models\Configs;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class NotificationTest extends TestCase
{
	public function testNotificationSetting(): void
	{
		AccessControl::log_as_id(0);

		// save initial value
		$init_config_value = Configs::get_value('new_photos_notification');

		$response = $this->postJson('/api/Settings::setNewPhotosNotification', [
			'new_photos_notification' => '1',
		]);
		$response->assertNoContent();
		static::assertEquals('1', Configs::get_value('new_photos_notification'));

		// set to initial
		Configs::set('new_photos_notification', $init_config_value);
	}

	public function testSetupUserEmail(): void
	{
		$users_test = new UsersUnitTest($this);
		$sessions_test = new SessionUnitTest($this);

		// add email to admin
		AccessControl::log_as_id(0);
		$users_test->update_email('test@test.com');

		// add new user
		$users_test->add('uploader', 'uploader');

		$sessions_test->logout();
	}

	/**
	 * TODO: Figure out if this test even tests anything related to notification; it appears to me as if this test simply uploads a file, but does not even assert that a notification has been sent.
	 */
	public function testUploadAndNotify(): void
	{
		$sessions_test = new SessionUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);
		$photos_tests = new PhotosUnitTest($this);

		// login as new user
		$sessions_test->login('uploader', 'uploader');

		// add new album
		$albumID = $albums_tests->add(null, 'test_album')->offsetGet('id');

		$photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		);

		$albums_tests->delete([$albumID]);

		// logout
		$sessions_test->logout();
	}

	public function testMailNotifications(): void
	{
		// save initial value
		$init_config_value = Configs::get_value('new_photos_notification');
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

		Configs::set('new_photos_notification', $init_config_value);
	}

	public function testClearNotifications(): void
	{
		$users_test = new UsersUnitTest($this);
		$sessions_test = new SessionUnitTest($this);

		// remove user, email & notifications
		AccessControl::log_as_id(0);

		$users_test->update_email(null);

		$response = $users_test->list();
		$t = json_decode($response->getContent());
		$user_id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $user_id,
			'username' => 'uploader',
			'may_upload' => true,
			'is_locked' => false,
		]);

		$users_test->delete($user_id);

		$sessions_test->logout();
	}
}
