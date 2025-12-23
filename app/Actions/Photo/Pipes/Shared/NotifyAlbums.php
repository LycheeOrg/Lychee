<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Actions\User\Notify;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;

/**
 * Notify by email if a picture has been added.
 */
class NotifyAlbums implements PhotoPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		$count_albums = DB::table(PA::PHOTO_ALBUM)
			->where('photo_id', $state->getPhoto()->id)
			->count();

		if ($count_albums > 0) {
			$notify = new Notify($this->config_manager);
			$notify->do($state->getPhoto());
		}

		return $next($state);
	}
}