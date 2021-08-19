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
		$response->assertStatus(200);
		$this->assertEquals('1', Configs::get_value('new_photos_notification'));

		// set to initial
		Configs::set('new_photos_notification', $init_config_value);
	}

	public function testSetupUserEmail()
	{
		$users_test = new UsersUnitTest();
		$sessions_test = new SessionUnitTest();

		// add email to admin
		AccessControl::log_as_id(0);
		$users_test->update_email($this, 'test@test.com');

		// add new user
		$users_test->add($this, 'uploader', 'uploader', '1', '0');

		$sessions_test->logout($this);
	}

	/**
	 * TODO: Figure out if this test even tests anything related to notification; it appears to me as if this test simply uploads a file, but does not even assert that a notification has been sent.
	 */
	public function testUploadAndNotify()
	{
		$sessions_test = new SessionUnitTest();
		$albums_tests = new AlbumsUnitTest($this);
		$photos_tests = new PhotosUnitTest($this);

		// login as new user
		$sessions_test->login($this, 'uploader', 'uploader');

		// add new album
		$albumID = $albums_tests->add('0', 'test_album')->offsetGet('id');

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
		$sessions_test->logout($this);
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

		Mail::fake('test@test.com')->send(new PhotosAdded($photos));

		Mail::assertSent(PhotosAdded::class);

		Configs::set('new_photos_notification', $init_config_value);
	}

	public function testClearNotifications()
	{
		$users_test = new UsersUnitTest();
		$sessions_test = new SessionUnitTest();

		// remove user, email & notifications
		AccessControl::log_as_id(0);

		$users_test->update_email($this, '');

		$response = $users_test->list($this, 'true');
		$t = json_decode($response->getContent());
		$user_id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $user_id,
			'username' => 'uploader',
			'upload' => 1,
			'lock' => 0,
		]);

		$users_test->delete($this, $user_id, 'true');

		$sessions_test->logout($this);
	}
}
