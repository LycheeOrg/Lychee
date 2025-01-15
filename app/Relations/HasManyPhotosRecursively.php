<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\NotImplementedException;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * @disregard
 *
 * @extends BaseHasManyPhotos<Album>
 */
class HasManyPhotosRecursively extends BaseHasManyPhotos
{
	protected AlbumQueryPolicy $albumQueryPolicy;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		parent::__construct($owningAlbum);
	}

	public function getParent(): Album
	{
		/**
		 * We know that the parent is of type `Album`,
		 * because it was set in the constructor as `$owningAlbum`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 */
		return $this->parent;
	}

	/**
	 * Adds the constraints for single owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * single albums are fetched.
	 *
	 * @throws InternalLycheeException
	 */
	public function addConstraints(): void
	{
		if (static::$constraints) {
			$this->addEagerConstraints([$this->getParent()]);
		}
	}

	/**
	 * Adds the constraints for a list of owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * list of owning albums are fetched.
	 * The unified result of the query is mapped to the specific albums
	 * by {@link HasManyPhotosRecursively::match()}.
	 *
	 * @param Album[] $albums an array of {@link \App\Models\Album} whose photos are loaded
	 *
	 * @return void
	 *
	 * @throws InternalLycheeException
	 */
	public function addEagerConstraints(array $albums): void
	{
		if (count($albums) !== 1) {
			throw new NotImplementedException('eagerly fetching all photos of an album is not implemented for multiple albums');
		}

		$this->photoQueryPolicy
			->applySearchabilityFilter(
				query: $this->getRelationQuery(),
				origin: $albums[0],
				include_nsfw: true
			);
	}

	/**
	 * @return Collection<int,\App\Models\Photo>
	 */
	public function getResults(): Collection
	{
		/** @var Album|null $album */
		$album = $this->parent;
		if ($album === null || !Gate::check(AlbumPolicy::CAN_ACCESS, $album)) {
			return $this->related->newCollection();
		} else {
			return parent::getResults();
		}
	}

	/**
	 * Maps a collection of eagerly fetched photos to the given owning albums.
	 *
	 * This method is called by the framework after the unified result of
	 * photos has been fetched by {@link HasManyPhotosRecursively::addEagerConstraints()}.
	 *
	 * @param Album[]                           $albums   the list of owning albums
	 * @param Collection<int,\App\Models\Photo> $photos   collection of {@link Photo} models which needs to be mapped to the albums
	 * @param string                            $relation the name of the relation
	 *
	 * @return array<int,Album>
	 *
	 * @throws NotImplementedException
	 */
	public function match(array $albums, Collection $photos, $relation): array
	{
		if (count($albums) !== 1) {
			throw new NotImplementedException('eagerly fetching all photos of an album is not implemented for multiple albums');
		}
		/** @var Album $album */
		$album = $albums[0];

		if (!Gate::check(AlbumPolicy::CAN_ACCESS, $album)) {
			$album->setRelation($relation, $this->related->newCollection());
		} else {
			$sorting = $album->getEffectivePhotoSorting();
			$photos = $photos->sortBy(
				$sorting->column->value,
				in_array($sorting->column, SortingDecorator::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR,
				$sorting->order === OrderSortingType::DESC
			)->values();
			$album->setRelation($relation, $photos);
		}

		return $albums;
	}
}
