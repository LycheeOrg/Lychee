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
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;

/**
 * @disregard
 *
 * @extends BaseHasManyPhotos<TagAlbum>
 */
class HasManyPhotosByTag extends BaseHasManyPhotos
{
	public function __construct(TagAlbum $owning_album)
	{
		parent::__construct($owning_album);
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

		$tag_ids = DB::table('tag_albums_tags')->where('album_id', '=', $album->id)
			->select('tag_id')
			->pluck('tag_id')->all();

		$tag_ids = $album->relationLoaded('tags')
			? $album->tags->pluck('id')->all()
			: DB::table('tag_albums_tags')
			->where('album_id', '=', $album->id)
			->pluck('tag_id')
			->all();
		$tag_ids = array_values(array_unique($tag_ids));


		if (Configs::getValueAsBool('TA_override_visibility')) {
			$this->photo_query_policy
				->applySensitivityFilter(
					$this->getRelationQuery(),
					origin: null,
					include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_tag_albums')
				)
				->where(fn(Builder $q) => $this->getPhotoIdsWithTags($q, $tag_ids, $album->is_and));
		} else {
			$this->photo_query_policy
				->applySearchabilityFilter(
					$this->getRelationQuery(),
					origin: null,
					include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_tag_albums')
				)
				->where(fn(Builder $q) => $this->getPhotoIdsWithTags($q, $tag_ids, $album->is_and));
		}
	}

	/**
	 * @param Builder &$query
	 * @param int[]   $tags_ids
	 * @param bool    $is_and
	 *
	 * @return void
	 */
	private function getPhotoIdsWithTags(Builder &$query, array $tags_ids, bool $is_and): void
	{
		// If no tags provided, no photos should match
		if (count($tags_ids) === 0) {
			$query->whereRaw('1 = 0');

			return;
		}

		$tag_count = count($tags_ids);
		if ($is_and) {
			$query->whereExists(
				fn(BaseBuilder $q) => $q->select(['photo_id', DB::raw('COUNT(tag_id) AS num')])
					->from('photos_tags')
					->whereIn('photos_tags.tag_id', $tags_ids)
					->whereColumn('photos_tags.photo_id', 'photos.id')
					->groupBy('photos_tags.photo_id')
					->havingRaw('COUNT(DISTINCT photos_tags.tag_id) = ?', [$tag_count])
			);
		} else {
			$query->whereExists(
				fn(BaseBuilder $q) => $q->select('photo_id')
					->from('photos_tags')
					->whereIn('photos_tags.tag_id', $tags_ids)
					->whereColumn('photos_tags.photo_id', 'photos.id')
			);
		}
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
