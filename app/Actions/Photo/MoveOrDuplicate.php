<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Shop\PurchasableService;
use App\Actions\User\Notify;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Purchasable;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoveOrDuplicate
{
	public function __construct(
		private PurchasableService $purchasable_service,
		protected readonly ConfigManager $config_manager,
	) {
	}

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

			foreach ($photos as $photo) {
				$this->applyToPurchasable($photo->id, $from_album->get_id(), $to_album?->get_id());
			}
		}

		$notify = new Notify($this->config_manager);
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$notify->do($photo);
		}
	}

	/**
	 * This function is called only when moving a photo from one album to another.
	 * If we do a duplication, then we do not flag the dupplicate as purchasable.
	 *
	 * Now considering the following cases while moving from
	 * album A to album B, where A has a purchasable P1 for photo X:
	 * - If B is null (root album), we delete P1.
	 * - If B already has a purchasable P2 for photo X, we do nothing (delete P1, keep P2).
	 * - If B has no purchasable for photo X, we "move" P1 to B.
	 */
	private function applyToPurchasable(string $photo_id, string $from_album_id, ?string $new_album_id): void
	{
		$purchasable = Purchasable::query()
			->where('photo_id', $photo_id)
			->where('album_id', $from_album_id)
			->first();

		// There is no purchasable in the source album, so nothing to do.
		if ($purchasable === null) {
			return;
		}

		// We are moving to root album
		if ($new_album_id === null) {
			// Moving to root album: delete the purchasable
			$this->purchasable_service->deletePurchasable($purchasable);

			return;
		}

		// There is already a purchasable there so we do nothing.
		if (Purchasable::query()->where('photo_id', $photo_id)->where('album_id', $new_album_id)->exists()) {
			// We delete the purchasable in the source album
			$this->purchasable_service->deletePurchasable($purchasable);

			return;
		}

		$purchasable->album_id = $new_album_id;
		$purchasable->save();
	}
}
