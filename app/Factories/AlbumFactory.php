<?php

namespace App\Factories;

use App\Contracts\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
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
	public const BUILTIN_SMARTS = [
		UnsortedAlbum::ID => UnsortedAlbum::class,
		StarredAlbum::ID => StarredAlbum::class,
		PublicAlbum::ID => PublicAlbum::class,
		RecentAlbum::ID => RecentAlbum::class,
	];

	/**
	 * Returns an existing instance of an album with the given ID or fails
	 * with an exception.
	 *
	 * @param string $albumId       the ID of the requested album
	 * @param bool   $withRelations indicates if the relations of an
	 *                              album (i.e. photos and sub-albums,
	 *                              if applicable) shall be loaded, too.
	 *
	 * @return AbstractAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException  thrown, if no album with the given ID exists
	 * @throws InvalidSmartIdException should not be thrown; otherwise this
	 *                                 indicates an internal bug
	 */
	public function findAbstractAlbumOrFail(string $albumId, bool $withRelations = true): AbstractAlbum
	{
		if ($this->isBuiltInSmartAlbum($albumId)) {
			return $this->createSmartAlbum($albumId, $withRelations);
		}

		return $this->findBaseAlbumOrFail($albumId, $withRelations);
	}

	/**
	 * Returns an existing model instance of an album with the given ID or
	 * fails with an exception.
	 *
	 * @param string $albumId       the ID of the requested album
	 * @param bool   $withRelations indicates if the relations of an
	 *                              album (i.e. photos and sub-albums,
	 *                              if applicable) shall be loaded, too.
	 *
	 * @return BaseAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException thrown, if no album with the given ID exists
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function findBaseAlbumOrFail(string $albumId, bool $withRelations = true): BaseAlbum
	{
		try {
			if ($withRelations) {
				return Album::query()->with(['photos', 'children', 'photos.size_variants'])->findOrFail($albumId);
			} else {
				return Album::query()->findOrFail($albumId);
			}
		} catch (ModelNotFoundException $e) {
			if ($withRelations) {
				return TagAlbum::query()->with(['photos'])->findOrFail($albumId);
			} else {
				return TagAlbum::query()->findOrFail($albumId);
			}
		}
	}

	/**
	 * Returns a collection of {@link AbstractAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $albumIDs a list of IDs
	 *
	 * @return Collection<AbstractAlbum> a possibly empty list of
	 *                                   {@link AbstractAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findAbstractAlbumsOrFail(array $albumIDs): Collection
	{
		// Remove root (ID===`null`) and duplicates
		$albumIDs = array_diff(array_unique($albumIDs), [null]);
		$smartAlbumIDs = array_intersect($albumIDs, array_keys(self::BUILTIN_SMARTS));
		$modelAlbumIDs = array_diff($albumIDs, array_keys(self::BUILTIN_SMARTS));

		$smartAlbums = [];
		foreach ($smartAlbumIDs as $smartID) {
			try {
				$smartAlbums[] = $this->createSmartAlbum($smartID);
			} catch (InvalidSmartIdException) {
				// must not happen, because we limited search to
				// `self::BUILTIN_SMARTS` above
			}
		}

		return new Collection(array_merge(
			$smartAlbums,
			$this->findBaseAlbumsOrFail($modelAlbumIDs)->all()
		));
	}

	/**
	 * Returns a collection of {@link BaseAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $albumIDs a list of IDs
	 *
	 * @return Collection<BaseAlbum> a possibly empty list of {@link BaseAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findBaseAlbumsOrFail(array $albumIDs): Collection
	{
		// Remove root.
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$albumIDs = array_diff(array_unique($albumIDs), [null]);

		$result = new Collection(array_merge(
			TagAlbum::query()->findMany($albumIDs)->all(),
			Album::query()->findMany($albumIDs)->all(),
		));

		if ($result->count() !== count($albumIDs)) {
			throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, $albumIDs);
		}

		return $result;
	}

	/**
	 * Returns a collection of {@link \App\SmartAlbums\BaseSmartAlbum} with
	 * one instance for each built-in smart album.
	 *
	 * @param bool $withRelations Eagerly loads the relation
	 *                            {@link BaseSmartAlbum::photos()}
	 *                            for each smart album
	 *
	 * @return Collection
	 *
	 * @throws InvalidSmartIdException
	 */
	public function getAllBuiltInSmartAlbums(bool $withRelations = true): Collection
	{
		$smartAlbums = new Collection();
		foreach (self::BUILTIN_SMARTS as $smartAlbumId => $smartAlbumClass) {
			$smartAlbums->push($this->createSmartAlbum($smartAlbumId, $withRelations));
		}

		return $smartAlbums;
	}

	/**
	 * Checks if the given album ID denotes one of the built-in smart albums.
	 *
	 * @param string $albumId
	 *
	 * @return bool true, if the album ID refers to a built-in smart album
	 */
	public function isBuiltInSmartAlbum(string $albumId): bool
	{
		return array_key_exists($albumId, self::BUILTIN_SMARTS);
	}

	/**
	 * Returns the instance of the built-in smart album with the designated ID.
	 *
	 * @param string $smartAlbumId  the ID of the smart album
	 * @param bool   $withRelations Eagerly loads the relation
	 *                              {@link BaseSmartAlbum::photos()}
	 *                              for the smart album
	 *
	 * @return BaseSmartAlbum
	 *
	 * @throws InvalidSmartIdException
	 */
	public function createSmartAlbum(string $smartAlbumId, bool $withRelations = true): BaseSmartAlbum
	{
		if (!$this->isBuiltInSmartAlbum($smartAlbumId)) {
			throw new InvalidSmartIdException($smartAlbumId);
		}

		/** @var BaseSmartAlbum $smartAlbum */
		$smartAlbum = call_user_func([self::BUILTIN_SMARTS[$smartAlbumId], 'getInstance']);
		if ($withRelations) {
			// Just try to get the photos.
			// This loads the relation from DB and caches it.
			$ignore = $smartAlbum->photos;
		}

		return $smartAlbum;
	}
}
