<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Constants\PhotoAlbum as PA;
use App\Mail\PhotosAdded;
use App\Models\BaseAlbumImpl;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Console\Command;
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

	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		if (!$this->config_manager->getValueAsBool('new_photos_notification')) {
			return 0;
		}
		$users = User::query()->whereNotNull('email')->get();

		/** @var User $user */
		foreach ($users as $user) {
			$photos = [];

			foreach ($user->unreadNotifications()->get() as $notification) {
				/** @var Photo|null $photo */
				$photo = Photo::query()
					->with(['size_variants'])
					->find($notification->data['id']);

				if ($photo !== null) {
					$thumb_url = $photo->size_variants->getThumb()?->url;

					// Mail clients do not like relative paths.
					// if url does not start with 'http', it is not absolute...
					if (!Str::startsWith('http', $thumb_url)) {
						$thumb_url = URL::asset($thumb_url);
					}

					BaseAlbumImpl::query()->join(PA::PHOTO_ALBUM, PA::ALBUM_ID, '=', 'base_albums.id')
						->where(PA::PHOTO_ID, '=', $photo->id)
						->get()
						->each(function (BaseAlbumImpl $album) use (&$photos, $photo, $thumb_url): void {
							$album_id = $album->id;
							$title = $album->title;

							if (!isset($photos[$album_id])) {
								$photos[$album_id] = [
									'name' => $title,
									'photos' => [],
								];
							}

							$photos[$album_id]['photos'][$photo->id] = [
								'title' => $photo->title,
								'thumb' => $thumb_url,
								'link' => route('gallery', ['albumId' => $album_id, 'photoId' => $photo->id]),
							];
						});
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
