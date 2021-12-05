<?php

namespace App\Actions\Album;

use App\Models\Album;
use App\Models\Logs;
use App\Models\Photo;
use Kalnoy\Nestedset\QueryBuilder as NSQueryBuilder;

class Merge extends Action
{
	/**
	 * Merges the content of the given source albums (photos and sub-albums)
	 * into the target.
	 *
	 * @param string   $albumID
	 * @param string[] $sourceAlbumIDs
	 */
	public function do(string $albumID, array $sourceAlbumIDs): void
	{
		$targetAlbum = $this->albumFactory->findOrFail($albumID, false);
		if (!($targetAlbum instanceof Album)) {
			$msg = 'Merge is only possible for real albums';
			Logs::error(__METHOD__, __LINE__, $msg);
			throw new \InvalidArgumentException($msg);
		}

		// Merge photos of source albums into target
		Photo::query()
			->whereIn('album_id', $sourceAlbumIDs)
			->update(['album_id' => $targetAlbum->id]);

		// Merge sub-albums of source albums into target
		// ! we have to do it via Model::save() in order to not break the tree
		$albums = Album::query()->whereIn('parent_id', $sourceAlbumIDs)->get();
		foreach ($albums as $album) {
			$album->parent_id = $targetAlbum->id;
			$album->save();
		}

		// Now we delete the source albums
		// ! we have to do it via Model::delete() in order to not break the tree
		$albums = Album::query()->whereIn('id', $sourceAlbumIDs)->get();
		foreach ($albums as $album) {
			$album->delete();
		}

		/** @var NSQueryBuilder $q */
		$q = Album::query();
		if ($q->isBroken()) {
			$errors = $q->countErrors();
			$sum = $errors['oddness'] + $errors['duplicates'] + $errors['wrong_parent'] + $errors['missing_parent'];
			Logs::warning(__METHOD__, __LINE__, 'Tree is broken with ' . $sum . ' errors.');
			$q->fixTree();
			Logs::notice(__METHOD__, __LINE__, 'Tree has been fixed.');
		}

		// Reset ownership
		$targetAlbum->descendants()->update(['owner_id' => $targetAlbum->owner_id]);
		$targetAlbum->all_photos()->update(['owner_id' => $targetAlbum->owner_id]);
	}
}
