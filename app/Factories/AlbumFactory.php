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
	public function findAbstractAlbumOrFail(string $album_i_d, bool $with_relations = true): AbstractAlbum
	{
		$smart_album_type = SmartAlbumType::tryFrom($album_i_d);
		if ($smart_album_type !== null) {
			return $this->createSmartAlbum($smart_album_type, $with_relations);
		}

		return $this->findBaseAlbumOrFail($album_i_d, $with_relations);
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
	public function findNullalbleAbstractAlbumOrFail(?string $album_i_d, bool $with_relations = true): ?AbstractAlbum
	{
		if ($album_i_d === null) {
			return null;
		}

		return $this->findAbstractAlbumOrFail($album_i_d, $with_relations);
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
	public function findBaseAlbumOrFail(string $album_i_d, bool $with_relations = true): BaseAlbum
	{
		$album_query = Album::query();
		$tag_album_query = TagAlbum::query();

		if ($with_relations) {
			$album_query->with(['access_permissions', 'photos', 'children', 'photos.size_variants']);
			$tag_album_query->with(['photos']);
		}

		try {
			return $album_query->findOrFail($album_i_d);
		} catch (ModelNotFoundException) {
			try {
				return $tag_album_query->findOrFail($album_i_d);
			} catch (ModelNotFoundException) {
				throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, [$album_i_d]);
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
	public function findAbstractAlbumsOrFail(array $album_i_ds, bool $with_relations = true): Collection
	{
		// Remove root (ID===`null`) and duplicates
		$album_i_ds = array_diff(array_unique($album_i_ds), [null]);
		$smart_album_i_ds = array_intersect($album_i_ds, SmartAlbumType::values());
		$model_album_i_ds = array_diff($album_i_ds, SmartAlbumType::values());

		$smart_albums = [];
		foreach ($smart_album_i_ds as $smart_i_d) {
			$smart_album_type = SmartAlbumType::from($smart_i_d);
			$smart_albums[] = $this->createSmartAlbum($smart_album_type, $with_relations);
		}

		return new Collection(array_merge(
			$smart_albums,
			$this->findBaseAlbumsOrFail($model_album_i_ds, $with_relations)->all()
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
	public function findBaseAlbumsOrFail(array $album_i_ds, bool $with_relations = true): Collection
	{
		// Remove root.
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$album_i_ds = array_diff(array_unique($album_i_ds), [null]);

		$tag_album_query = TagAlbum::query();
		$album_query = Album::query();

		if ($with_relations) {
			$tag_album_query->with(['photos']);
			$album_query->with(['photos', 'children', 'photos.size_variants']);
		}

		/** @var Collection<int,Album|TagAlbum> $result */
		$result = new Collection(array_merge(
			$tag_album_query->findMany($album_i_ds)->all(),
			$album_query->findMany($album_i_ds)->all(),
		));

		if ($result->count() !== count($album_i_ds)) {
			throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, $album_i_ds);
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
	public function getAllBuiltInSmartAlbums(bool $with_relations = true): Collection
	{
		$smart_albums = new Collection();
		collect(SmartAlbumType::cases())
			->filter(fn (SmartAlbumType $s) => $s->is_enabled())
			->each(fn (SmartAlbumType $s) => $smart_albums->put($s->value, $this->createSmartAlbum($s, $with_relations)));

		return $smart_albums;
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
	public function createSmartAlbum(SmartAlbumType $smart_album_id, bool $with_relations = true): BaseSmartAlbum
	{
		/** @var BaseSmartAlbum $smartAlbum */
		$smart_album = call_user_func(self::BUILTIN_SMARTS_CLASS[$smart_album_id->value] . '::getInstance');
		if ($with_relations) {
			// Just try to get the photos.
			// This loads the relation from DB and caches it.
			// @phpstan-ignore-next-line : PhpStan will complain about unused variable.
			$ignore = $smart_album->photos;
		}

		return $smart_album;
	}
}
