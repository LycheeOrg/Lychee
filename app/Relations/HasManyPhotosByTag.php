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
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @disregard
 *
 * @extends BaseHasManyPhotos<TagAlbum>
 */
class HasManyPhotosByTag extends BaseHasManyPhotos
{
	public function __construct(TagAlbum $owningAlbum)
	{
		parent::__construct($owningAlbum);
	}

	/**
	 * Adds the constraints for single owning album to the base query.
	 *
	 * This method is called by the framework, if the photos of a
	 * single tag albums are fetched.
	 *
	 * @return void
	 *
	 * @throws InternalLycheeException
	 */
	public function addConstraints(): void
	{
		if (static::$constraints) {
			$this->addEagerConstraints([$this->parent]);
		}
	}

	/**
	 * Adds the constraints for a list of owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * list of owning albums are fetched.
	 * The unified result of the query is mapped to the specific albums
	 * by {@link HasManyPhotosByTag::match()}.
	 *
	 * @param TagAlbum[] $albums an array of {@link \App\Models\TagAlbum} whose photos are loaded
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
		/** @var TagAlbum $album */
		$album = $albums[0];
		$tags = $album->show_tags;

		$this->photoQueryPolicy
			->applySearchabilityFilter(
				$this->getRelationQuery(),
				origin: null,
				include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_smart_albums')
			)
			->where(function (Builder $q) use ($tags) {
				// Filter for requested tags
				foreach ($tags as $tag) {
					$q->where('tags', 'like', '%' . trim($tag) . '%');
				}
			});
	}

	/**
	 * Maps a collection of eagerly fetched photos to the given owning albums.
	 *
	 * This method is called by the framework after the unified result of
	 * photos has been fetched by {@link HasManyPhotosByTag::addEagerConstraints()}.
	 *
	 * @param TagAlbum[]                        $albums   the list of owning albums
	 * @param Collection<int,\App\Models\Photo> $photos   collection of {@link Photo} models which needs to be mapped to the albums
	 * @param string                            $relation the name of the relation
	 *
	 * @return array<int,TagAlbum>
	 *
	 * @throws NotImplementedException
	 */
	public function match(array $albums, Collection $photos, $relation): array
	{
		if (count($albums) !== 1) {
			throw new NotImplementedException('eagerly fetching all photos of an album is not implemented for multiple albums');
		}
		/** @var TagAlbum $album */
		$album = $albums[0];
		$sorting = $album->getEffectivePhotoSorting();

		$photos = $photos->sortBy(
			$sorting->column->value,
			in_array($sorting->column, SortingDecorator::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR,
			$sorting->order === OrderSortingType::DESC
		)->values();
		$album->setRelation($relation, $photos);

		return $albums;
	}
}
