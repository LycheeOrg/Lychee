<?php

namespace App\Factories;

use App\Contracts\BaseAlbum;
use App\Models\Album;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class AlbumFactory
{
	const BUILTIN_SMARTS = [
		UnsortedAlbum::ID => UnsortedAlbum::class,
		StarredAlbum::ID => StarredAlbum::class,
		PublicAlbum::ID => PublicAlbum::class,
		RecentAlbum::ID => RecentAlbum::class,
	];

	/**
	 * Returns an existing instance of an album with the given ID or fails
	 * with on exception.
	 *
	 * @param int|string $albumId the ID of the requested album
	 *
	 * @return BaseAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException thrown, if no album with the given ID exists
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function findOrFail($albumId): BaseAlbum
	{
		if ($this->isBuiltInSmartAlbum($albumId)) {
			return $this->createSmartAlbum($albumId);
		}
		try {
			return Album::query()->findOrFail($albumId);
		} catch (ModelNotFoundException $e) {
			return TagAlbum::query()->findOrFail($albumId);
		}
	}

	/**
	 * Returns a collection of {@link BaseAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param array $albumIDs a list of IDs; a mix of integer IDs (for
	 *                        proper models) and string IDs (for built-in
	 *                        smart albums) is acceptable
	 *
	 * @return Collection a possibly empty list of {@link BaseAlbum}
	 */
	public function findWhereIDsIn(array $albumIDs): Collection
	{
		$smartAlbumIDs = array_intersect($albumIDs, self::BUILTIN_SMARTS);
		$modelAlbumIDs = array_diff($albumIDs, self::BUILTIN_SMARTS);

		$smartAlbums = [];
		foreach ($smartAlbumIDs as $smartID) {
			$smartAlbums[] = $this->createSmartAlbum($smartID);
		}

		return new Collection(array_merge(
			$smartAlbums,
			TagAlbum::query()->findMany($modelAlbumIDs)->all(),
			Album::query()->findMany($modelAlbumIDs)->all(),
		));
	}

	/**
	 * Returns a collection of {@link \App\SmartAlbums\BaseSmartAlbum} with one instance for each built-in smart album.
	 *
	 * @return Collection
	 */
	public function getAllBuiltInSmartAlbums(): Collection
	{
		$smartAlbums = new Collection();
		foreach (self::BUILTIN_SMARTS as $smartAlbumId => $smartAlbumClass) {
			$smartAlbums->push($this->createSmartAlbum($smartAlbumId));
		}

		return $smartAlbums;
	}

	/**
	 * Checks if the given album ID denotes one of the built-in smart albums.
	 *
	 * @param int|string $albumId
	 *
	 * @return bool true, if the album ID refers to a built-in smart album
	 */
	public function isBuiltInSmartAlbum($albumId): bool
	{
		return array_key_exists($albumId, self::BUILTIN_SMARTS);
	}

	protected function createSmartAlbum(string $smartAlbumId): BaseSmartAlbum
	{
		return call_user_func([self::BUILTIN_SMARTS[$smartAlbumId], 'getInstance']);
	}
}
