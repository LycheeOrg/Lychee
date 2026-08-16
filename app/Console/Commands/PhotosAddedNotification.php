<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Constants\PhotoAlbum as PA;
use App\Mail\PhotosAdded;
use App\Models\BaseAlbumImpl;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
			$this->warn('Skipped: the "new_photos_notification" setting is disabled. Enable it in the admin settings to send email notifications.');

			return Command::SUCCESS;
		}

		$users = User::query()->whereNotNull('email')->get();

		if ($users->isEmpty()) {
			$this->warn('No users with an email address were found. Nothing to do.');

			return Command::SUCCESS;
		}

		$this->info('Checking pending photo notifications for ' . $users->count() . ' user(s) with an email address...');

		$emails_sent = 0;
		$emails_failed = 0;
		$photos_notified = 0;
		$users_skipped = 0;

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
					if (!Str::startsWith($thumb_url, 'http')) {
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

			if (count($photos) === 0) {
				$users_skipped++;
				$this->line("  - {$user->email}: no pending notifications.", null, 'v');
				continue;
			}

			$photo_count = collect($photos)->sum(fn (array $album) => count($album['photos']));

			try {
				Mail::to($user->email)->send(new PhotosAdded($photos));
				$user->notifications()->delete();

				$emails_sent++;
				$photos_notified += $photo_count;
				$this->line("  - {$user->email}: sent notification for {$photo_count} photo(s).", null, 'v');
			} catch (\Throwable $e) {
				$emails_failed++;
				$this->error("  - {$user->email}: failed to send notification ({$e->getMessage()}).");
				Log::error('Failed to send photo-added notification to ' . $user->email . ': ' . $e->getMessage());
			}
		}

		if ($emails_sent === 0 && $emails_failed === 0) {
			$this->info("No pending photo notifications found for any of the {$users->count()} user(s).");
		} else {
			$this->info("Sent {$emails_sent} notification email(s) covering {$photos_notified} photo(s). {$users_skipped} user(s) had nothing pending.");
		}

		if ($emails_failed > 0) {
			$this->error("{$emails_failed} notification email(s) failed to send. Check the mail configuration and storage/logs for details.");

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
