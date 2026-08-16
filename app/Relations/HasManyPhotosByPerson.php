<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\NotImplementedException;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Services\PersonAlbumMatcher;
use Illuminate\Database\Eloquent\Collection;

/**
 * @disregard
 *
 * @extends BaseHasManyPhotos<PersonAlbum>
 */
class HasManyPhotosByPerson extends BaseHasManyPhotos
{
	public function __construct(PersonAlbum $owning_album, ?User $for_user = null, bool $user_is_set = false)
	{
		// Sic! We must set these before calling the parent constructor,
		// since it triggers `addEagerConstraints()` (see BaseHasManyPhotos).
		$this->for_user = $for_user;
		$this->user_is_set = $user_is_set;
		parent::__construct($owning_album);
	}

	/**
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
	 * @param PersonAlbum[] $albums
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
		/** @var PersonAlbum $album */
		$album = $albums[0];

		/** @var ?User $user */
		$user = $this->resolveUser();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$ids_query = app(PersonAlbumMatcher::class)->buildMatchingPhotoIdsQuery($album, $user, $unlocked_album_ids);

		$this->getRelationQuery()->whereIn('photos.id', $ids_query);
	}

	/**
	 * @param PersonAlbum[]                     $albums
	 * @param Collection<int,\App\Models\Photo> $photos
	 * @param string                            $relation
	 *
	 * @return array<int,PersonAlbum>
	 *
	 * @throws NotImplementedException
	 */
	public function match(array $albums, Collection $photos, $relation): array
	{
		if (count($albums) !== 1) {
			throw new NotImplementedException('eagerly fetching all photos of an album is not implemented for multiple albums');
		}
		/** @var PersonAlbum $album */
		$album = $albums[0];
		$sorting = $album->getEffectivePhotoSorting();

		$photos = $photos->sortBy(
			$sorting->column->toColumn(),
			in_array($sorting->column, SortingDecorator::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR,
			$sorting->order === OrderSortingType::DESC
		)->values();
		$album->setRelation($relation, $photos);

		return $albums;
	}
}
