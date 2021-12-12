<?php

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Mail\PhotosAdded;
use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class NotificationTest extends TestCase
{
	public function testNotificationSetting()
	{
		AccessControl::log_as_id(0);

		// save initial value
		$init_config_value = Configs::get_value('new_photos_notification');

		$response = $this->json('POST', '/api/Settings::setNewPhotosNotification', [
			'new_photos_notification' => '1',
		]);
		$response->assertNoContent();
		$this->assertEquals('1', Configs::get_value('new_photos_notification'));

		// set to initial
		Configs::set('new_photos_notification', $init_config_value);
	}

	public function testSetupUserEmail()
	{
		$users_test = new UsersUnitTest($this);
		$sessions_test = new SessionUnitTest($this);

		// add email to admin
		AccessControl::log_as_id(0);
		$users_test->update_email('test@test.com');

		// add new user
		$users_test->add('uploader', 'uploader', true, false);

		$sessions_test->logout();
	}

	/**
	 * TODO: Figure out if this test even tests anything related to notification; it appears to me as if this test simply uploads a file, but does not even assert that a notification has been sent.
	 */
	public function testUploadAndNotify()
	{
		$sessions_test = new SessionUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);
		$photos_tests = new PhotosUnitTest($this);

		// login as new user
		$sessions_test->login('uploader', 'uploader');

		// add new album
		$albumID = $albums_tests->add(null, 'test_album')->offsetGet('id');

		// upload photo to the album
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile(
			'public/uploads/import/night.jpg',
			'night.jpg',
			'image/jpeg',
			null,
			true
		);

		$photos_tests->upload($file, $albumID);

		$albums_tests->delete($albumID);

		// logout
		$sessions_test->logout();
	}

	public function testMailNotifications()
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

	public function testClearNotifications()
	{
		$users_test = new UsersUnitTest($this);
		$sessions_test = new SessionUnitTest($this);

		// remove user, email & notifications
		AccessControl::log_as_id(0);

		$users_test->update_email('');

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
