<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Factories;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class AlbumFactory
{
	public const BUILTIN_SMARTS_CLASS = [
		SmartAlbumType::UNSORTED->value => UnsortedAlbum::class,
		SmartAlbumType::STARRED->value => StarredAlbum::class,
		SmartAlbumType::RECENT->value => RecentAlbum::class,
		SmartAlbumType::ON_THIS_DAY->value => OnThisDayAlbum::class,
	];

	/**
	 * Returns an existing instance of an album with the given ID or fails
	 * with an exception.
	 *
	 * @param string $albumID       the ID of the requested album
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
	public function findAbstractAlbumOrFail(string $albumID, bool $withRelations = true): AbstractAlbum
	{
		$smartAlbumType = SmartAlbumType::tryFrom($albumID);
		if ($smartAlbumType !== null) {
			return $this->createSmartAlbum($smartAlbumType, $withRelations);
		}

		return $this->findBaseAlbumOrFail($albumID, $withRelations);
	}

	/**
	 * Same as above but in the case of albumID being null, it returns null.
	 *
	 * @param string|null $albumID       the ID of the requested album
	 * @param bool        $withRelations indicates if the relations of an
	 *                                   album (i.e. photos and sub-albums,
	 *                                   if applicable) shall be loaded, too.
	 *
	 * @return AbstractAlbum|null the album for the ID or null if ID is null
	 *
	 * @throws ModelNotFoundException  thrown, if no album with the given ID exists
	 * @throws InvalidSmartIdException should not be thrown; otherwise this
	 *                                 indicates an internal bug
	 */
	public function findNullalbleAbstractAlbumOrFail(?string $albumID, bool $withRelations = true): ?AbstractAlbum
	{
		if ($albumID === null) {
			return null;
		}

		return $this->findAbstractAlbumOrFail($albumID, $withRelations);
	}

	/**
	 * Returns an existing model instance of an album with the given ID or
	 * fails with an exception.
	 *
	 * @param string $albumID       the ID of the requested album
	 * @param bool   $withRelations indicates if the relations of an
	 *                              album (i.e. photos and sub-albums,
	 *                              if applicable) shall be loaded, too.
	 *
	 * @return BaseAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException thrown, if no album with the given ID exists
	 *
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function findBaseAlbumOrFail(string $albumID, bool $withRelations = true): BaseAlbum
	{
		$albumQuery = Album::query();
		$tagAlbumQuery = TagAlbum::query();

		if ($withRelations) {
			$albumQuery->with(['access_permissions', 'photos', 'children', 'photos.size_variants']);
			$tagAlbumQuery->with(['photos']);
		}

		try {
			return $albumQuery->findOrFail($albumID);
		} catch (ModelNotFoundException) {
			try {
				return $tagAlbumQuery->findOrFail($albumID);
			} catch (ModelNotFoundException) {
				throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, [$albumID]);
			}
		}
	}

	/**
	 * Returns a collection of {@link AbstractAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $albumIDs      a list of IDs
	 * @param bool     $withRelations indicates if the relations of an
	 *                                album (i.e. photos and sub-albums,
	 *                                if applicable) shall be loaded, too.
	 *
	 * @return Collection<int,AbstractAlbum> a possibly empty list of
	 *                                       {@link AbstractAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findAbstractAlbumsOrFail(array $albumIDs, bool $withRelations = true): Collection
	{
		// Remove root (ID===`null`) and duplicates
		$albumIDs = array_diff(array_unique($albumIDs), [null]);
		$smartAlbumIDs = array_intersect($albumIDs, SmartAlbumType::values());
		$modelAlbumIDs = array_diff($albumIDs, SmartAlbumType::values());

		$smartAlbums = [];
		foreach ($smartAlbumIDs as $smartID) {
			$smartAlbumType = SmartAlbumType::from($smartID);
			$smartAlbums[] = $this->createSmartAlbum($smartAlbumType, $withRelations);
		}

		/** @phpstan-ignore-next-line phpstan stan complain of incompatibility of types while both are subtypes... */
		return new Collection(array_merge(
			$smartAlbums,
			$this->findBaseAlbumsOrFail($modelAlbumIDs, $withRelations)->all()
		));
	}

	/**
	 * Returns a collection of {@link BaseAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $albumIDs      a list of IDs
	 * @param bool     $withRelations indicates if the relations of an
	 *                                album (i.e. photos and sub-albums,
	 *                                if applicable) shall be loaded, too.
	 *
	 * @return Collection<int,Album|TagAlbum> a possibly empty list of {@link BaseAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findBaseAlbumsOrFail(array $albumIDs, bool $withRelations = true): Collection
	{
		// Remove root.
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$albumIDs = array_diff(array_unique($albumIDs), [null]);

		$tagAlbumQuery = TagAlbum::query();
		$albumQuery = Album::query();

		if ($withRelations) {
			$tagAlbumQuery->with(['photos']);
			$albumQuery->with(['photos', 'children', 'photos.size_variants']);
		}

		/** @var Collection<int,Album|TagAlbum> $result */
		$result = new Collection(array_merge(
			$tagAlbumQuery->findMany($albumIDs)->all(),
			$albumQuery->findMany($albumIDs)->all(),
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
	 * @return Collection<int,BaseSmartAlbum>
	 *
	 * @throws InvalidSmartIdException
	 */
	public function getAllBuiltInSmartAlbums(bool $withRelations = true): Collection
	{
		$smartAlbums = new Collection();
		collect(SmartAlbumType::cases())
			->filter(fn (SmartAlbumType $s) => $s->is_enabled())
			->each(fn (SmartAlbumType $s) => $smartAlbums->put($s->value, $this->createSmartAlbum($s, $withRelations)));

		return $smartAlbums;
	}

	/**
	 * Returns the instance of the built-in smart album with the designated ID.
	 *
	 * @param SmartAlbumType $smartAlbumId  the ID of the smart album
	 * @param bool           $withRelations Eagerly loads the relation
	 *                                      {@link BaseSmartAlbum::photos()}
	 *                                      for the smart album
	 *
	 * @return BaseSmartAlbum
	 *
	 * @throws InvalidSmartIdException
	 */
	public function createSmartAlbum(SmartAlbumType $smartAlbumId, bool $withRelations = true): BaseSmartAlbum
	{
		/** @var BaseSmartAlbum $smartAlbum */
		$smartAlbum = call_user_func(self::BUILTIN_SMARTS_CLASS[$smartAlbumId->value] . '::getInstance');
		if ($withRelations) {
			// Just try to get the photos.
			// This loads the relation from DB and caches it.
			// @phpstan-ignore-next-line : PhpStan will complain about unused variable.
			$ignore = $smartAlbum->photos;
		}

		return $smartAlbum;
	}
}
