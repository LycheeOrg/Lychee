<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\NsfwSensitiveNoAlbumAction;
use App\Enum\NsfwStatus;
use App\Models\Album;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplyNsfwAlbumSensitivityJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public function __construct(
		public string $photo_id,
	) {
	}

	public function handle(ConfigManager $config_manager): void
	{
		$photo = Photo::with('albums')->find($this->photo_id);

		if ($photo === null) {
			Log::warning("ApplyNsfwAlbumSensitivityJob: photo {$this->photo_id} not found.");

			return;
		}

		if ($photo->albums->isEmpty()) {
			$this->handleNoAlbumFallback($photo, $config_manager);

			return;
		}

		foreach ($photo->albums as $album) {
			$album_with_recursive = Album::query()
				->addVirtualIsRecursiveNSFW()
				->find($album->id);

			if ($album_with_recursive === null) {
				continue;
			}

			if ($album_with_recursive->is_recursive_nsfw) {
				Log::info("ApplyNsfwAlbumSensitivityJob: album {$album->id} already has ancestor NSFW, skipping.");
				continue;
			}

			$album->is_nsfw = true;
			$album->save();
			Log::info("ApplyNsfwAlbumSensitivityJob: marked album {$album->id} as NSFW for photo {$this->photo_id}.");
		}
	}

	private function handleNoAlbumFallback(Photo $photo, ConfigManager $config_manager): void
	{
		$action = NsfwSensitiveNoAlbumAction::tryFrom(
			$config_manager->getValueAsString('ai_vision_nsfw_sensitive_no_album_action')
		) ?? NsfwSensitiveNoAlbumAction::SKIP;

		if ($action === NsfwSensitiveNoAlbumAction::SKIP) {
			Log::warning("ApplyNsfwAlbumSensitivityJob: photo {$this->photo_id} has no albums (unsorted), skipping album marking.");

			return;
		}

		$photo->nsfw_status = NsfwStatus::REVIEW;
		$photo->is_validated = false;
		$photo->save();
		Log::info("ApplyNsfwAlbumSensitivityJob: photo {$this->photo_id} has no albums, moderated as fallback.");
	}
}
