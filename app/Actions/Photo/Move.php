<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\User\Notify;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;

class Move
{
	/**
	 * Duplicates a set of photos.
	 *
	 * @param Collection<int,Photo> $photos the source photos
	 * @param Album|null            $album  the destination album; `null` means root album
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function do(Collection $photos, ?Album $album): void
	{
		$notify = new Notify();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$photo->album_id = $album?->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$photo->setRelation('album', $album);
			if ($album !== null) {
				$photo->owner_id = $album->owner_id;
			}
			$photo->save();
			$notify->do($photo);
		}

		Album::query()->whereIn('header_id', $photos->map(fn (Photo $p) => $p->id))->update(['header_id' => null]);
	}
}
