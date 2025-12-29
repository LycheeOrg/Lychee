<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
use Illuminate\Support\Facades\DB;
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
	protected PhotoSortingCriterion $sorting;

	public function __construct(Album $parent)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photo_query_policy = resolve(PhotoQueryPolicy::class);
		$this->sorting = PhotoSortingCriterion::createDefault();
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
		// Priority 1: Explicit cover
		if ($album->cover_id !== null) {
			return $album->cover_id;
		}

		/** @var ?User $user */
		$user = Auth::user();
		$album_policy = resolve(AlbumPolicy::class);

		// Priority 2: Max-privilege cover for admin or owner
		if ($user?->may_administrate === true || $album_policy->isOwnerOrAncestorOwner($user, $album)) {
			return $album->auto_cover_id_max_privilege;
		}

		// Priority 3: Least-privilege cover for public
		return $album->auto_cover_id_least_privilege;
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
	 * Builds a query to eagerly load the thumbnails of a sequence of albums.
	 *
	 * Now uses pre-computed cover IDs (auto_cover_id_max_privilege and
	 * auto_cover_id_least_privilege) instead of expensive runtime queries.
	 *
	 * @param array<Album> $models
	 */
	public function addEagerConstraints(array $models): void
	{
		// Build mapping of album_id => cover_id for each album
		$album_to_cover = [];
		foreach ($models as $album) {
			$cover_id = $this->selectCoverIdForAlbum($album);
			if ($cover_id !== null) {
				$album_to_cover[$album->id] = $cover_id;
			}
		}

		if (count($album_to_cover) === 0) {
			// No covers to load - make query return empty result
			$this->getRelationQuery()->whereRaw('1 = 0');

			return;
		}

		// Select photos with their album association
		$this->getRelationQuery()
			->select([
				'photos.id as id',
				'photos.type as type',
				DB::raw('CASE ' .
					collect($album_to_cover)->map(fn ($cover_id, $album_id) => "WHEN photos.id = '$cover_id' THEN '$album_id'"
					)->implode(' ') .
					' END as covered_album_id'),
			])
			->whereIn('photos.id', array_values($album_to_cover));
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
		$dictionary = $results->mapToDictionary(function ($result) {
			/** @var Photo&object{covered_album_id: string} $result */
			return [$result->covered_album_id => $result];
		})->all();

		// Match photos to albums using the covered_album_id
		/** @var Album $album */
		foreach ($models as $album) {
			$album_id = $album->id;
			if (isset($dictionary[$album_id])) {
				$cover = reset($dictionary[$album_id]);
				$album->setRelation($relation, Thumb::createFromPhoto($cover));
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

		$cover_id = $this->selectCoverIdForAlbum($album);

		if ($cover_id !== null) {
			// Use pre-computed cover ID (explicit, max-privilege, or least-privilege)
			$photo = Photo::query()->with(['size_variants' => (fn ($r) => Thumb::sizeVariantsFilter($r))])->find($cover_id);

			return $photo !== null ? Thumb::createFromPhoto($photo) : null;
		} else {
			// Fallback to legacy query if no cover available
			return Thumb::createFromQueryable(
				$this->getRelationQuery(),
				$this->sorting,
			);
		}
	}
}