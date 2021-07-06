<?php

namespace App\Console\Commands;

use App\Mail\PhotosAdded;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PhotosAddedNotification extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:photos_added_notification';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send email notifications for newly added photos';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$settings = Configs::get();

		if ($settings['new_photos_notification']) {
			$users = User::whereNotNull('email')->get();

			foreach ($users as $user) {
				$photos = [];

				foreach ($user->unreadNotifications as $notification) {
					$photo = Photo::find($notification->data['id']);

					if ($photo && $photo->thumbUrl) {
						if (!isset($photos[$photo->album_id])) {
							$photos[$photo->album_id] = [
								'name' => $photo->album->title,
								'photos' => [],
							];
						}

						logger(Storage::url(Photo::VARIANT_2_PATH_PREFIX[Photo::VARIANT_THUMB] . '/' . $photo->thumbUrl));

						$photos[$photo->album_id]['photos'][$photo->id] = [
							'thumb' => Storage::url(Photo::VARIANT_2_PATH_PREFIX[Photo::VARIANT_THUMB] . '/' . $photo->thumbUrl),
							'link' => config('app.url') . '/r/' . $photo->album_id . '/' . $photo->id,
						];
					}
				}

				if (count($photos) > 0) {
					try {
						Mail::to($user->email)->send(new PhotosAdded($photos));
						// $user->notifications()->delete();
					} catch (Exception $e) {
						Logs::error(__METHOD__, __LINE__, 'Failed to send email notification for ' . $user->username);
					}
				}
			}
		}
	}
}
