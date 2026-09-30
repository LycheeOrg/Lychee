<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Relations;

use App\Actions\Album\AutoCoverRows;
use App\DTO\PhotoSortingCriterion;
use App\Models\Album;
use App\Models\Builders\PhotoBuilder;
use App\Models\Extensions\Thumb;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Builder<Photo>
 *
 * @extends Relation<Photo,Album,Thumb|null>
 *
 * @disregard P1037
 */
class HasAlbumThumb extends Relation
{
	protected PhotoQueryPolicy $photo_query_policy;

	public function __construct(Album $parent)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photo_query_policy = resolve(PhotoQueryPolicy::class);
		parent::__construct(
			Photo::query()
				->with(['size_variants' => (fn ($r) => Thumb::sizeVariantsFilter($r))]),
			$parent
		);
	}

	/**
	 * @return PhotoBuilder<Photo>
	 */
	protected function getRelationQuery(): PhotoBuilder
	{
		/**
		 * We know that the internal query is of type `PhotoBuilder`,
		 * because it was set in the constructor as `Photo::query()`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 */
		return $this->query;
	}

	/**
	 * The cover photo of the album for the current viewer, if any is stored.
	 *
	 * Priority:
	 * 1. Explicit cover_id (if set)
	 * 2. The viewer's precomputed automatic cover row (Feature 076):
	 *    the owner's row for an admin or the owner, else the `NULL` row
	 *    or the row of the single user the album is shared with
	 *
	 * @param Album $album
	 *
	 * @return Photo|null
	 */
	protected function selectCoverForAlbum(Album $album): ?Photo
	{
		if ($album->cover_id !== null) {
			return $album->cover;
		}

		return AutoCoverRows::forViewer($album->autoCoverRows, $album->owner_id, Auth::user())?->photo;
	}

	/**
	 * The id of {@see self::selectCoverForAlbum()}, without loading the photo.
	 *
	 * @param Album $album
	 *
	 * @return string|null
	 */
	protected function selectCoverIdForAlbum(Album $album): ?string
	{
		return $album->cover_id ?? AutoCoverRows::forViewer($album->autoCoverRows, $album->owner_id, Auth::user())?->photo_id;
	}

	/**
	 * Adds the constraints for a single album.
	 *
	 * Uses the stored cover of {@see self::selectCoverIdForAlbum()}, and
	 * falls back to the live searchability query when none is stored.
	 */
	public function addConstraints(): void
	{
		if (static::$constraints) {
			/** @var Album $album */
			$album = $this->parent;
			$cover_id = $this->selectCoverIdForAlbum($album);

			if ($cover_id !== null) {
				// @phpstan-ignore-next-line
				$this->where('photos.id', '=', $cover_id);
			} else {
				// Fallback to legacy behavior if no cover available
				$user = Auth::user();
				$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

				$this->photo_query_policy
					->applySearchabilityFilter(
						query: $this->getRelationQuery(),
						user: $user,
						unlocked_album_ids: $unlocked_album_ids,
						origin: $album,
						include_nsfw: $album->is_nsfw);
			}
		}
	}

	/**
	 * We do not eager load any covers.
	 * This relation is only meaningful for single albums.
	 * In case of multiple album we use the preloaded `cover` and
	 * `autoCoverRows` relations (see {@link Album::$with}).
	 *
	 * @param array<Album> $models
	 */
	public function addEagerConstraints(array $models): void
	{
		// No covers to load - make query return empty result
		$this->getRelationQuery()->whereRaw('1 = 0');
	}

	/**
	 * @param array<int,Album> $models   an array of albums models whose thumbnails shall be initialized
	 * @param string           $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,Album> the array of album models
	 */
	public function initRelation(array $models, $relation): array
	{
		foreach ($models as $model) {
			$model->setRelation($relation, null);
		}

		return $models;
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param array<int,Album>      $models   an array of parent models
	 * @param Collection<int,Photo> $results  the unified collection of all child models of all parent models
	 * @param string                $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,Album>
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		/** @var Album $album */
		foreach ($models as $album) {
			$cover = $this->selectCoverForAlbum($album);
			$album->setRelation($relation, $cover === null ? null : Thumb::createFromPhoto($cover));
		}

		return $models;
	}

	public function getResults(): ?Thumb
	{
		/** @var Album $album */
		$album = $this->parent;
		if ($album === null || !Gate::check(AlbumPolicy::CAN_ACCESS, $album)) {
			return null;
		}

		// We do not execute a query when a cover is stored: `Album` is always
		// eagerly loaded with its `cover` and `autoCoverRows` relations.
		// See {@link Album::$with}
		$cover = $this->selectCoverForAlbum($album);
		if ($cover !== null) {
			return Thumb::createFromPhoto($cover);
		}

		return Thumb::createFromQueryable(
			$this->getRelationQuery(),
			PhotoSortingCriterion::createDefault()
		);
	}
}