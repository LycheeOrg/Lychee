<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Relations;

use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Extensions\Thumb;
use App\Models\Photo;
use App\Models\User;
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
	 * @return FixedQueryBuilder<Photo>
	 */
	protected function getRelationQuery(): FixedQueryBuilder
	{
		/**
		 * We know that the internal query is of type `FixedQueryBuilder`,
		 * because it was set in the constructor as `Photo::query()`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 */
		return $this->query;
	}

	/**
	 * Determine the cover type to use for the album.
	 *
	 * @param Album $album
	 *
	 * @return string
	 */
	protected function getCoverTypeForAlbum(Album $album): string
	{
		if ($album->cover_id !== null) {
			return 'cover_id';
		}

		/** @var ?User $user */
		$user = Auth::user();

		// Priority 2: Max-privilege cover for admin or owner
		if ($user?->may_administrate === true || $album->owner_id === $user?->id) {
			return 'auto_cover_id_max_privilege';
		}

		// Priority 3: Least-privilege cover for public
		return 'auto_cover_id_least_privilege';
	}

	/**
	 * Select the appropriate cover ID based on user privileges.
	 *
	 * Priority:
	 * 1. Explicit cover_id (if set)
	 * 2. auto_cover_id_max_privilege (if admin or owns album/ancestor)
	 * 3. auto_cover_id_least_privilege (public view)
	 *
	 * @param Album $album
	 *
	 * @return string|null
	 */
	protected function selectCoverIdForAlbum(Album $album): ?string
	{
		return match ($this->getCoverTypeForAlbum($album)) {
			'cover_id' => $album->cover_id,
			'auto_cover_id_max_privilege' => $album->auto_cover_id_max_privilege,
			'auto_cover_id_least_privilege' => $album->auto_cover_id_least_privilege,
			default => null,
		};
	}

	/**
	 * Adds the constraints for a single album.
	 *
	 * Determines which cover photo to use based on priority:
	 * 1. Explicit cover_id (if set)
	 * 2. auto_cover_id_max_privilege (if user is admin or owns album/ancestor)
	 * 3. auto_cover_id_least_privilege (public view)
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
	 * In case of multiple album we use the preloaded values from
	 * `cover_id`, `auto_cover_id_max_privilege`, and `auto_cover_id_least_privilege`.
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
			$cover_type = $this->getCoverTypeForAlbum($album);
			if ($cover_type === 'cover_id' && $album->cover_id !== null) {
				// We do not need to do anything here, because we already have the cover
				// loaded via the `cover` relation of `Album`.
				$album->setRelation($relation, Thumb::createFromPhoto($album->cover));
			} elseif ($cover_type === 'auto_cover_id_max_privilege' && $album->auto_cover_id_max_privilege !== null) {
				$album->setRelation($relation, Thumb::createFromPhoto($album->max_privilege_cover));
			} elseif ($cover_type === 'auto_cover_id_least_privilege' && $album->auto_cover_id_least_privilege !== null) {
				$album->setRelation($relation, Thumb::createFromPhoto($album->min_privilege_cover));
			} else {
				$album->setRelation($relation, null);
			}
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

		// We do not execute a query, if `cover_id` is set, because `Album`
		// is always eagerly loaded with its cover and hence, we already
		// have it.
		// See {@link Album::with}
		$cover_type = $this->getCoverTypeForAlbum($album);
		if ($cover_type === 'cover_id' && $album->cover_id !== null) {
			// We do not need to do anything here, because we already have the cover
			// loaded via the `cover` relation of `Album`.
			return Thumb::createFromPhoto($album->cover);
		} elseif ($cover_type === 'auto_cover_id_max_privilege' && $album->auto_cover_id_max_privilege !== null) {
			return Thumb::createFromPhoto($album->max_privilege_cover);
		} elseif ($cover_type === 'auto_cover_id_least_privilege' && $album->auto_cover_id_least_privilege !== null) {
			return Thumb::createFromPhoto($album->min_privilege_cover);
		} else {
			return Thumb::createFromQueryable(
				$this->getRelationQuery(),
				PhotoSortingCriterion::createDefault()
			);
		}
	}
}