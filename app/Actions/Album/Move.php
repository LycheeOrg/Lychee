<?php

namespace App\Actions\Album;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Move extends Action
{
	/**
	 * Moves the given albums into the target.
	 *
	 * @param string $targetAlbumID
	 * @param array  $albumIDs
	 *
	 * @throws ModelNotFoundException
	 * @throws InternalLycheeException
	 * @throws ModelDBException
	 */
	public function do(string $targetAlbumID, array $albumIDs): void
	{
		if (empty($targetAlbumID)) {
			$targetAlbumID = null;
			$targetAlbum = null;
		} else {
			/** @var Album $targetAlbum */
			$targetAlbum = Album::query()->findOrFail($targetAlbumID);
		}

		try {
			$albums = Album::query()->whereIn('id', $albumIDs)->get();
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}

		// Merge source albums to target
		// We have to do it via Model::save() in order to not break the tree
		/** @var Album $album */
		foreach ($albums as $album) {
			$album->parent_id = $targetAlbumID;
			$album->save();
		}
		// Tree should be updated by itself here.

		if ($targetAlbum) {
			// Update ownership to owner of target album
			try {
				$descendantIDs = $targetAlbum->descendants()->pluck('id');
			} catch (\InvalidArgumentException $e) {
				throw new QueryBuilderException($e);
			}
			// Note, the property `owner_id` is defined on the base class of
			// all model albums.
			// For optimization, we do not load the album models but perform
			// the update directly on the database.
			// Hence, we must use `BaseAlbumImpl`.
			try {
				BaseAlbumImpl::query()->whereIn('id', $descendantIDs)->update(['owner_id' => $targetAlbum->owner_id]);
				$descendantIDs[] = $targetAlbum->getKey();
				Photo::query()->whereIn('id', $descendantIDs)->update(['owner_id' => $targetAlbum->owner_id]);
			} catch (\InvalidArgumentException $e) {
				throw new QueryBuilderException($e);
			}
		}
	}
}
