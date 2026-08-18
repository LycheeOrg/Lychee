<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Constants\PhotoAlbum as PA;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\PhotoSaved;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Merge
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 * @throws QueryBuilderException
	 */
	public function do(Album $target_album, Collection $albums): void
	{
		$origin_ids = $albums->pluck('id')->all();

		// Select all photos ids of the source albums
		$photos_ids = DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::ALBUM_ID, $origin_ids)
			->pluck(PA::PHOTO_ID)->all();

		// Delete the existing links at destination (avoid duplicates key contraint)
		DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::PHOTO_ID, $photos_ids)
			->where(PA::ALBUM_ID, '=', $target_album->id)
			->delete();

		// Insert the new links
		DB::table(PA::PHOTO_ALBUM)
			->insert(array_map(fn (string $id) => ['photo_id' => $id, 'album_id' => $target_album->id], $photos_ids));

		// Delete the existing links from the origins
		DB::table(PA::PHOTO_ALBUM)
			->whereIn(PA::PHOTO_ID, $photos_ids)
			->whereIn(PA::ALBUM_ID, $origin_ids)
			->delete();

		// Re-linking photos via raw pivot writes above bypasses every photo-level
		// model event, which the PersonAlbum "matching albums" cache (see
		// AlbumRepository::getMatchingAlbumsForPersonPaginated()) depends on to
		// know a photo's containing albums changed. Dispatch a single batched
		// PhotoSaved (precise, cheap eviction) rather than a coarse flush —
		// merges are common enough that a global flush would defeat this cache.
		PhotoSaved::dispatchIf($photos_ids !== [], $photos_ids);

		// Merge sub-albums of source albums into target
		$target_gains_children = false;
		/** @var Album $album */
		foreach ($albums as $album) {
			foreach ($album->children as $child_album) {
				$target_gains_children = true;
				$has_descendants = !$child_album->isLeaf();

				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$target_album->appendNode($child_album);

				AlbumListingCacheFlushRequested::dispatchIf($has_descendants);
			}
		}

		AlbumChildrenChanged::dispatchIf($target_gains_children, [$target_album->id]);

		// Now we delete the source albums
		// We must use the special `Delete` action in order to not break the
		// tree.
		(new Delete())->do($albums->pluck('id')->values()->all());

		$target_album->fixOwnershipOfChildren();
	}
}