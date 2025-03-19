<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class Merge
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @param Album                 $targetAlbum
	 * @param Collection<int,Album> $albums
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 * @throws QueryBuilderException
	 */
	public function do(Album $target_album, Collection $albums): void
	{
		// Merge photos of source albums into target
		Photo::query()
			->whereIn('album_id', $albums->pluck('id'))
			->update(['album_id' => $target_album->id]);

		// Merge sub-albums of source albums into target
		/** @var Album $album */
		foreach ($albums as $album) {
			foreach ($album->children as $child_album) {
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$target_album->appendNode($child_album);
			}
		}

		// Now we delete the source albums
		// We must use the special `Delete` action in order to not break the
		// tree.
		// The returned `FileDeleter` can be ignored as all photos have been
		// moved to the new location.
		(new Delete())->do($albums->pluck('id')->values()->all());

		$target_album->fixOwnershipOfChildren();
	}
}
