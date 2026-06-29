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
use App\Models\Builders\PhotoBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;

/**
 * @disregard
 *
 * @extends BaseHasManyPhotos<PersonAlbum>
 */
class HasManyPhotosByPerson extends BaseHasManyPhotos
{
	public function __construct(PersonAlbum $owning_album)
	{
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

		$person_ids = $album->relationLoaded('persons')
			? $album->persons->pluck('id')->all()
			: DB::table('person_albums_persons')
			->where('album_id', '=', $album->id)
			->pluck('person_id')
			->all();
		$person_ids = array_values(array_unique($person_ids));

		$user = \Illuminate\Support\Facades\Auth::user();
		$unlocked_album_ids = \App\Policies\AlbumPolicy::getUnlockedAlbumIDs();

		$config_manager = app(ConfigManager::class);

		/** @var PhotoBuilder<Photo> $ids_query */
		$ids_query = Photo::query()->select('photos.id');

		if ($config_manager->getValueAsBool('PA_override_visibility')) {
			$this->photo_query_policy
				->applySensitivityFilter(
					query: $ids_query,
					user: $user,
					origin: null,
					include_nsfw: !$config_manager->getValueAsBool('hide_nsfw_in_person_albums')
				)
				->where(fn (Builder $q) => $this->getPhotoIdsWithPersons($q, $person_ids, $album->is_and));
		} else {
			$this->photo_query_policy
				->applySearchabilityFilter(
					query: $ids_query,
					user: $user,
					unlocked_album_ids: $unlocked_album_ids,
					origin: null,
					include_nsfw: !$config_manager->getValueAsBool('hide_nsfw_in_person_albums')
				)
				->where(fn (Builder $q) => $this->getPhotoIdsWithPersons($q, $person_ids, $album->is_and));
		}

		$this->getRelationQuery()->whereIn('photos.id', $ids_query);
	}

	/**
	 * @param Builder  &$query
	 * @param string[] $person_ids
	 * @param bool     $is_and
	 *
	 * @return void
	 */
	private function getPhotoIdsWithPersons(Builder &$query, array $person_ids, bool $is_and): void
	{
		if (count($person_ids) === 0) {
			$query->whereRaw('1 = 0');

			return;
		}

		$person_count = count($person_ids);
		if ($is_and) {
			$query->whereExists(
				fn (BaseBuilder $q) => $q->select(['photo_id', DB::raw('COUNT(DISTINCT person_id) AS num')])
					->from('faces')
					->whereIn('faces.person_id', $person_ids)
					->whereColumn('faces.photo_id', 'photos.id')
					->where('faces.is_dismissed', '=', false)
					->groupBy('faces.photo_id')
					->havingRaw('COUNT(DISTINCT faces.person_id) = ?', [$person_count])
			);
		} else {
			$query->whereExists(
				fn (BaseBuilder $q) => $q->select('photo_id')
					->from('faces')
					->whereIn('faces.person_id', $person_ids)
					->whereColumn('faces.photo_id', 'photos.id')
					->where('faces.is_dismissed', '=', false)
			);
		}
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
