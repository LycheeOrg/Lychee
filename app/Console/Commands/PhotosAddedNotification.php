<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Mail\PhotosAdded;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		if (!Configs::getValueAsBool('new_photos_notification')) {
			return 0;
		}
		$users = User::query()->whereNotNull('email')->get();

		/** @var User $user */
		foreach ($users as $user) {
			$photos = [];

			/** @var DatabaseNotification $notification */
			foreach ($user->unreadNotifications()->get() as $notification) {
				/** @var Photo|null $photo */
				$photo = Photo::query()
					->with(['size_variants', 'size_variants.sym_links'])
					->find($notification->data['id']);

				if ($photo !== null) {
					if (!isset($photos[$photo->album_id])) {
						$photos[$photo->album_id] = [
							'name' => $photo->album->title,
							'photos' => [],
						];
					}

					$thumbUrl = $photo->size_variants->getThumb()?->url;

					// Mail clients do not like relative paths.
					// if url does not start with 'http', it is not absolute...
					if (!Str::startsWith('http', $thumbUrl)) {
						$thumbUrl = URL::asset($thumbUrl);
					}

					// If the url config doesn't contain a trailing slash then add it
					if (str_ends_with(config('app.url'), '/')) {
						$trailing_slash = '';
					} else {
						$trailing_slash = '/';
					}

					$photos[$photo->album_id]['photos'][$photo->id] = [
						'title' => $photo->title,
						'thumb' => $thumbUrl,
						// TODO: Clean this up. There should be a better way to get the URL of a photo than constructing it manually
						'link' => config('app.url') . $trailing_slash . 'r/' . $photo->album_id . '/' . $photo->id,
					];
				}
			}

			if (count($photos) > 0) {
				Mail::to($user->email)->send(new PhotosAdded($photos));
				$user->notifications()->delete();
			}
		}

		return 0;
	}
}
