<?php

namespace App\Actions\Album;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Merge extends Action
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @param string $albumID
	 * @param array  $sourceAlbumIDs
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
		try {
			Photo::query()
				->whereIn('album_id', $sourceAlbumIDs)
				->update(['album_id' => $targetAlbum->id]);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}

		// Merge sub-albums of source albums into target
		// ! we have to do it via Model::save() in order to not break the tree
		try {
			$albums = Album::query()->whereIn('parent_id', $sourceAlbumIDs)->get();
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
		/** @var Album $album */
		foreach ($albums as $album) {
			$album->parent_id = $targetAlbum->id;
			$album->save();
		}

		// Now we delete the source albums
		// ! we have to do it via Model::delete() in order to not break the tree
		try {
			$albums = Album::query()->whereIn('id', $sourceAlbumIDs)->get();
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
		/** @var Album $album */
		foreach ($albums as $album) {
			$album->delete();
		}

		$q = Album::query();
		if ($q->isBroken()) {
			try {
				$errors = $q->countErrors();
			} catch (\InvalidArgumentException $e) {
				throw new QueryBuilderException($e);
			}
			$sum = $errors['oddness'] + $errors['duplicates'] + $errors['wrong_parent'] + $errors['missing_parent'];
			Logs::warning(__FUNCTION__, __LINE__, 'Tree is broken with ' . $sum . ' errors.');
			$q->fixTree();
			Logs::notice(__FUNCTION__, __LINE__, 'Tree has been fixed.');
		}

		// Reset ownership
		try {
			$targetAlbum->descendants()->update(['owner_id' => $targetAlbum->owner_id]);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
		$targetAlbum->all_photos()->update(['owner_id' => $targetAlbum->owner_id]);
	}
}
