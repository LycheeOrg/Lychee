<?php

namespace App\Actions\Album;

use App\Contracts\InternalLycheeException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Merge extends Action
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @param string   $albumID
	 * @param string[] $sourceAlbumIDs
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 * @throws InternalLycheeException
	 */
	public function do(string $albumID, array $sourceAlbumIDs): void
	{
		/** @var Album $targetAlbum */
		$targetAlbum = Album::query()->findOrFail($albumID);

		// Merge photos of source albums into target
		Photo::query()
			->whereIn('album_id', $sourceAlbumIDs)
			->update(['album_id' => $targetAlbum->id]);

		// Merge sub-albums of source albums into target
		$albums = Album::query()->whereIn('parent_id', $sourceAlbumIDs)->get();
		/** @var Album $album */
		foreach ($albums as $album) {
			// Don't set attribute `parent_id` manually, but use specialized
			// methods of the nested set `NodeTrait` to keep the enumeration
			// of the tree consistent
			// `appendNode` also internally calls `save` on the model
			$targetAlbum->appendNode($album);
		}

		// Now we delete the source albums
		// ! we have to do it via Model::delete() in order to not break the tree
		$albums = Album::query()->whereIn('id', $sourceAlbumIDs)->get();
		/** @var Album $album */
		foreach ($albums as $album) {
			$album->delete();
		}

		$targetAlbum->fixOwnershipOfChildren();
	}
}
