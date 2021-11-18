<?php

namespace App\Console\Commands;

use App\Mail\PhotosAdded;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;

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
	public function handle(): int
	{
		if (Configs::get_Value('new_photos_notification', '0') !== '1') {
			return 0;
		}
		$users = User::query()->whereNotNull('email')->get();

		/** @var User $user */
		foreach ($users as $user) {
			$photos = [];

			/** @var DatabaseNotification $notification */
			foreach ($user->unreadNotifications()->get() as $notification) {
				/** @var Photo $photo */
				$photo = Photo::query()->find($notification->data['id']);

				if ($photo) {
					if (!isset($photos[$photo->album_id])) {
						$photos[$photo->album_id] = [
							'name' => $photo->album->title,
							'photos' => [],
						];
					}

					$thumbUrl = $photo->size_variants->getSizeVariant(SizeVariant::THUMB)->url;
					logger($thumbUrl);

					// If the url config doesn't contain a trailing slash then add it
					if (substr(config('app.url'), -1) == '/') {
						$trailing_slash = '';
					} else {
						$trailing_slash = '/';
					}

					$photos[$photo->album_id]['photos'][$photo->id] = [
						'thumb' => $thumbUrl,
						// TODO: Clean this up. There should be a better way to get the URL of a photo than constructing it manually
						'link' => config('app.url') . $trailing_slash . 'r/' . $photo->album_id . '/' . $photo->id,
					];
				}
			}

			if (count($photos) > 0) {
				try {
					Mail::to($user->email)->send(new PhotosAdded($photos));
					$user->notifications()->delete();
				} catch (\Exception $e) {
					Logs::error(__METHOD__, __LINE__, 'Failed to send email notification for ' . $user->username);
				}
			}
		}

		return 0;
	}
}
