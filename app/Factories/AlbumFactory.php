<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Factories;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\Repositories\ConfigManager;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\BestPicturesAlbum;
use App\SmartAlbums\FiveStarsAlbum;
use App\SmartAlbums\FourStarsAlbum;
use App\SmartAlbums\HighlightedAlbum;
use App\SmartAlbums\MyBestPicturesAlbum;
use App\SmartAlbums\MyRatedPicturesAlbum;
use App\SmartAlbums\OneStarAlbum;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\ThreeStarsAlbum;
use App\SmartAlbums\TwoStarsAlbum;
use App\SmartAlbums\UnratedAlbum;
use App\SmartAlbums\UnsortedAlbum;
use App\SmartAlbums\UntaggedAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class AlbumFactory
{
	public const BUILTIN_SMARTS_CLASS = [
		SmartAlbumType::UNSORTED->value => UnsortedAlbum::class,
		SmartAlbumType::HIGHLIGHTED->value => HighlightedAlbum::class,
		SmartAlbumType::RECENT->value => RecentAlbum::class,
		SmartAlbumType::ON_THIS_DAY->value => OnThisDayAlbum::class,
		SmartAlbumType::UNTAGGED->value => UntaggedAlbum::class,
		SmartAlbumType::UNRATED->value => UnratedAlbum::class,
		SmartAlbumType::ONE_STAR->value => OneStarAlbum::class,
		SmartAlbumType::TWO_STARS->value => TwoStarsAlbum::class,
		SmartAlbumType::THREE_STARS->value => ThreeStarsAlbum::class,
		SmartAlbumType::FOUR_STARS->value => FourStarsAlbum::class,
		SmartAlbumType::FIVE_STARS->value => FiveStarsAlbum::class,
		SmartAlbumType::BEST_PICTURES->value => BestPicturesAlbum::class,
		SmartAlbumType::MY_RATED_PICTURES->value => MyRatedPicturesAlbum::class,
		SmartAlbumType::MY_BEST_PICTURES->value => MyBestPicturesAlbum::class,
	];

	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Returns an existing instance of an album with the given ID or fails
	 * with an exception.
	 *
	 * @param string $album_id       the ID of the requested album
	 * @param bool   $with_relations indicates if the relations of an album
	 *                               (i.e. photos and sub-albums, if applicable) shall be loaded, too.
	 *
	 * @return AbstractAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException  thrown, if no album with the given ID exists
	 * @throws InvalidSmartIdException should not be thrown; otherwise this indicates an internal bug
	 */
	public function findAbstractAlbumOrFail(string $album_id, bool $with_relations = true): AbstractAlbum
	{
		$smart_album_type = SmartAlbumType::tryFrom($album_id);
		if ($smart_album_type !== null) {
			return $this->createSmartAlbum($smart_album_type, $with_relations);
		}

		return $this->findBaseAlbumOrFail($album_id, $with_relations);
	}

	/**
	 * Same as above but in the case of albumID being null, it returns null.
	 *
	 * @param string|null $album_id       the ID of the requested album
	 * @param bool        $with_relations indicates if the relations of an album
	 *                                    (i.e. photos and sub-albums, if applicable) shall be loaded, too.
	 *
	 * @return AbstractAlbum|null the album for the ID or null if ID is null
	 *
	 * @throws ModelNotFoundException  thrown, if no album with the given ID exists
	 * @throws InvalidSmartIdException should not be thrown; otherwise this indicates an internal bug
	 */
	public function findNullalbleAbstractAlbumOrFail(?string $album_id, bool $with_relations = true): ?AbstractAlbum
	{
		if ($album_id === null) {
			return null;
		}

		return $this->findAbstractAlbumOrFail($album_id, $with_relations);
	}

	/**
	 * Returns an existing model instance of an album with the given ID or
	 * fails with an exception.
	 *
	 * @param string $album_id       the ID of the requested album
	 * @param bool   $with_relations indicates if the relations of an album
	 *                               (i.e. photos and sub-albums, if applicable) shall be loaded, too.
	 *
	 * @return BaseAlbum the album for the ID
	 *
	 * @throws ModelNotFoundException thrown, if no album with the given ID exists
	 *
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function findBaseAlbumOrFail(string $album_id, bool $with_relations = true): BaseAlbum
	{
		$album_query = Album::query()->with(['access_permissions']);
		$tag_album_query = TagAlbum::query()->with(['access_permissions']);

		if ($with_relations) {
			$album_query->with(['photos', 'children', 'children.owner', 'photos.size_variants', 'photos.statistics', 'photos.palette', 'photos.tags', 'photos.rating']);
			$tag_album_query->with(['tags', 'photos', 'photos.size_variants', 'photos.statistics', 'photos.palette', 'photos.tags', 'photos.rating']);
		}

		$ret = $album_query->find($album_id) ?? $tag_album_query->find($album_id);
		if ($ret === null) {
			throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, [$album_id]);
		}

		return $ret;
	}

	/**
	 * Returns a collection of {@link AbstractAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $album_ids      a list of IDs
	 * @param bool     $with_relations indicates if the relations of an album
	 *                                 (i.e. photos and sub-albums, if applicable) shall be loaded, too.
	 *
	 * @return Collection<int,AbstractAlbum> a possibly empty list of {@link AbstractAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findAbstractAlbumsOrFail(array $album_ids, bool $with_relations = true): Collection
	{
		// Remove root (ID===`null`) and duplicates
		$album_ids = array_diff(array_unique($album_ids), [null]);
		$smart_album_ids = array_intersect($album_ids, SmartAlbumType::values());
		$model_album_ids = array_diff($album_ids, SmartAlbumType::values());

		$smart_albums = [];
		foreach ($smart_album_ids as $smart_id) {
			$smart_album_type = SmartAlbumType::from($smart_id);
			$smart_albums[] = $this->createSmartAlbum($smart_album_type, $with_relations);
		}

		/** @var Collection<int,AbstractAlbum> */
		return new Collection(array_merge(
			$smart_albums,
			$this->findBaseAlbumsOrFail($model_album_ids, $with_relations)->all()
		));
	}

	/**
	 * Returns a collection of {@link BaseAlbum} instances whose IDs are
	 * contained in the given set of IDs.
	 *
	 * @param string[] $album_ids      a list of IDs
	 * @param bool     $with_relations indicates if the relations of an album
	 *                                 (i.e. photos and sub-albums, if applicable)
	 *                                 shall be loaded, too.
	 * @param bool     $albums_only    if true, only albums are returned, not tag albums
	 *
	 * @return ($albums_only is true ? Collection<int,Album> : Collection<int,Album|TagAlbum>) a possibly empty list of {@link BaseAlbum}
	 *
	 * @throws ModelNotFoundException
	 */
	public function findBaseAlbumsOrFail(array $album_ids, bool $with_relations = true, $albums_only = false): Collection
	{
		// Remove root.
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$album_ids = array_diff(array_unique($album_ids), [null]);

		$tag_album_query = TagAlbum::query();
		$album_query = Album::query();

		if ($with_relations) {
			$tag_album_query->with(['tags', 'photos', 'photos.size_variants', 'photos.statistics', 'photos.palette', 'photos.tags', 'photos.rating']);
			$album_query->with(['photos', 'children', 'photos.size_variants', 'photos.statistics', 'photos.palette', 'photos.tags', 'photos.rating']);
		}

		/** @var ($albums_only is true ? array<int,Album> : array<int,TagAlbum>)&array */
		$tag_albums = $albums_only ? [] : $tag_album_query->findMany($album_ids)->all(); /** @phpstan-ignore varTag.type */

		/** @var array<int,Album> $albums */
		$albums = $album_query->findMany($album_ids)->all(); /** @phpstan-ignore varTag.type */
		$result = new Collection(array_merge($tag_albums, $albums));

		if ($result->count() !== count($album_ids)) {
			throw (new ModelNotFoundException())->setModel(BaseAlbumImpl::class, $album_ids);
		}

		return $result;
	}

	/**
	 * Returns a collection of {@link \App\SmartAlbums\BaseSmartAlbum} with
	 * one instance for each built-in smart album.
	 *
	 * @param bool $with_relations Eagerly loads the relation {@link BaseSmartAlbum::photos()}
	 *                             for each smart album
	 *
	 * @return Collection<int,BaseSmartAlbum>
	 *
	 * @throws InvalidSmartIdException
	 */
	public function getAllBuiltInSmartAlbums(bool $with_relations = true): Collection
	{
		$smart_albums = new Collection();
		collect(SmartAlbumType::cases())
			->filter(fn (SmartAlbumType $s) => $s->is_enabled($this->config_manager))
			->each(fn (SmartAlbumType $s) => $smart_albums->put($s->value, $this->createSmartAlbum($s, $with_relations)));

		return $smart_albums;
	}

	/**
	 * Returns the instance of the built-in smart album with the designated ID.
	 *
	 * @param SmartAlbumType $smart_album_id the ID of the smart album
	 * @param bool           $with_relations Eagerly loads the relation {@link BaseSmartAlbum::photos()}
	 *                                       for the smart album
	 *
	 * @throws InvalidSmartIdException
	 */
	public function createSmartAlbum(SmartAlbumType $smart_album_id, bool $with_relations = true): BaseSmartAlbum
	{
		$smart_album = call_user_func(self::BUILTIN_SMARTS_CLASS[$smart_album_id->value] . '::getInstance', $this->config_manager);
		if ($with_relations) {
			// Just try to get the photos.
			// This loads the relation from DB and caches it.
			// @phpstan-ignore-next-line : PhpStan will complain about unused variable.
			$ignore = $smart_album->photos;
		}

		return $smart_album;
	}
}