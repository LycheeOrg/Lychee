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
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class NotificationTest extends TestCase
{
	public function testNotificationSetting(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('new_photos_notification');

		try {
			AccessControl::log_as_id(0);

			$response = $this->postJson('/api/Settings::setNewPhotosNotification', [
				'new_photos_notification' => '1',
			]);
			$response->assertNoContent();
			static::assertEquals('1', Configs::getValue('new_photos_notification'));
		} finally {
			// set to initial
			Configs::set('new_photos_notification', $init_config_value);
		}
	}

	public function testSetupUserEmail(): void
	{
		$users_test = new UsersUnitTest($this);
		$sessions_test = new SessionUnitTest($this);

		// add email to admin
		AccessControl::log_as_id(0);
		$users_test->update_email('test@test.com');

		$sessions_test->logout();
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
}
