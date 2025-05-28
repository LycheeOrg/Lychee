<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Album;
use Illuminate\Support\Facades\DB;

/**
 * This MUST be called after a first save() otherwise we do not have a photo id.
 */
class SetParent implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		if ($state->album instanceof Album) {
			if ($state->photo->id === null) {
				throw new LycheeLogicException('Photo Id is null, cannot set a parent album.');
			}

			// Avoid duplicates key constraint
			DB::table(PA::PHOTO_ALBUM)
				->where(PA::PHOTO_ID, '=', $state->photo->id)
				->where(PA::ALBUM_ID, '=', $state->album->id)
				->delete();

			// Insert the new link
			DB::table(PA::PHOTO_ALBUM)
				->insert([
					'photo_id' => $state->photo->id,
					'album_id' => $state->album->id,
				]);

			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->load('albums');
		}

		return $next($state);
	}
}