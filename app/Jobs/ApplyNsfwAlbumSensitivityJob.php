<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Models\Album;
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

	/**
	 * @param string[] $album_ids
	 */
	public function __construct(
		public array $album_ids,
	) {
	}

	public function handle(): void
	{
		if ($this->album_ids === []) {
			return;
		}

		foreach ($this->album_ids as $album_id) {
			$album = Album::query()
				->addVirtualIsRecursiveNSFW()
				->find($album_id);

			if ($album === null) {
				continue;
			}

			if ($album->is_recursive_nsfw) {
				Log::info("ApplyNsfwAlbumSensitivityJob: album {$album_id} already has ancestor NSFW, skipping.");
				continue;
			}

			$album->is_nsfw = true;
			$album->save();
			Log::info("ApplyNsfwAlbumSensitivityJob: marked album {$album_id} as NSFW.");
		}
	}
}
