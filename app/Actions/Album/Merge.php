<?php

namespace App\Actions\Album;

use App\Contracts\InternalLycheeException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Merge extends Action
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @param Album             $targetAlbum
	 * @param Collection<Album> $albums
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 * @throws InternalLycheeException
	 */
	public function do(Album $targetAlbum, Collection $albums): void
	{
		// Merge photos of source albums into target
		Photo::query()
			->whereIn('album_id', $albums->pluck('id'))
			->update(['album_id' => $targetAlbum->id]);

		// Merge sub-albums of source albums into target
		/** @var Album $album */
		foreach ($albums as $album) {
			foreach ($album->children as $childAlbum) {
				// Don't set attribute `parent_id` manually, but use specialized
				// methods of the nested set `NodeTrait` to keep the enumeration
				// of the tree consistent
				// `appendNode` also internally calls `save` on the model
				$targetAlbum->appendNode($childAlbum);
			}
		}

		// Now we delete the source albums
		// ! we have to do it via Model::delete() in order to not break the tree
		/** @var Album $album */
		foreach ($albums as $album) {
			$album->delete();
		}

		$targetAlbum->fixOwnershipOfChildren();
	}
}
