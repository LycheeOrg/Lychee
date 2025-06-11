<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\User\Notify;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoveOrDuplicate
{
	/**
	 * Move or Duplicates a set of photos.
	 *
	 * If $from_album = $to_album, this is a duplication.
	 * If $from_album != $to_album, this is a move.
	 *
	 * @param Collection<int,Photo> $photos     the source photos
	 * @param AbstractAlbum         $from_album the origin album; `null` means root album
	 * @param Album                 $to_album   the destination album; `null` means root album
	 *
	 * @return void
	 */
	public function do(Collection $photos, ?AbstractAlbum $from_album, ?Album $to_album): void
	{
		// Extract the photos Ids.
		$photos_ids = $photos->pluck('id')->all();

		if ($from_album !== null) {
			// Delete the existing links.
			DB::table(PA::PHOTO_ALBUM)
				->whereIn(PA::PHOTO_ID, $photos_ids)
				->where(PA::ALBUM_ID, '=', $from_album->get_id())
				->delete();
		}

		if ($to_album !== null) {
			// Delete the existing links at destination (avoid duplicates key contraint)
			// If $from === to this operation is not needed.
			DB::table(PA::PHOTO_ALBUM)
				->whereIn(PA::PHOTO_ID, $photos_ids)
				->where(PA::ALBUM_ID, '=', $to_album->id)
				->delete();

			// Add the new links.
			DB::table(PA::PHOTO_ALBUM)->insert(array_map(fn (string $id) => ['photo_id' => $id, 'album_id' => $to_album->id], $photos_ids));
		}

		// In case of move, we need to remove the header_id of said photos.
		if ($from_album !== null && $from_album->get_id() !== $to_album?->id) {
			Album::query()
				->where('id', '=', $from_album->get_id())
				->whereIn('header_id', $photos->map(fn (Photo $p) => $p->id))
				->update(['header_id' => null]);
		}

		$notify = new Notify();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$notify->do($photo);
		}
	}
}
