<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Constants\PersonAlbumPersons;
use App\Models\Builders\PhotoBuilder;
use App\Models\PersonAlbum;
use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Builds the "which photos match this PersonAlbum's criteria and are visible
 * to the given user" query.
 *
 * Extracted out of {@see \App\Relations\HasManyPhotosByPerson} so the exact
 * same predicate (AND/OR `is_and` semantics, visibility gating via
 * `PA_override_visibility`/`PA_override_searchability`/`hide_nsfw_in_person_albums`)
 * can also be reused by {@see \App\Repositories\AlbumRepository} to find the
 * real albums containing a matching photo. The two call sites must never
 * drift apart, or a PersonAlbum could list an album containing none of its
 * own listed photos (or vice versa).
 */
class PersonAlbumMatcher
{
	public function __construct(
		private readonly PhotoQueryPolicy $photo_query_policy,
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * The IDs of all persons attached to the given PersonAlbum that the given
	 * user is allowed to see (i.e. is_searchable persons, or all of them for
	 * an admin / when PA_override_searchability is set).
	 *
	 * @return string[]
	 */
	public function getVisiblePersonIds(PersonAlbum $album, ?User $user): array
	{
		$can_see_all_persons = $user?->may_administrate === true || $this->config_manager->getValueAsBool('PA_override_searchability');

		$person_ids = DB::table(PersonAlbumPersons::PERSON_ALBUM_PERSONS)
			->where(PersonAlbumPersons::ALBUM_ID, '=', $album->id)
			->when(
				!$can_see_all_persons,
				fn (BaseBuilder $q) => $q
				->join('persons', PersonAlbumPersons::PERSON_ID, '=', 'persons.id')
				->where('persons.is_searchable', '=', true)
			)
			->pluck('person_id')
			->all();

		return array_values(array_unique($person_ids));
	}

	/**
	 * Builds a `Photo::query()->select('photos.id')` builder restricted to
	 * photos which are visible to `$user` and match the PersonAlbum's
	 * person/AND-OR criteria.
	 *
	 * @param string[] $unlocked_album_ids session-scoped unlocked password-protected albums
	 *
	 * @return PhotoBuilder<Photo>
	 */
	public function buildMatchingPhotoIdsQuery(PersonAlbum $album, ?User $user, array $unlocked_album_ids): PhotoBuilder
	{
		$person_ids = $this->getVisiblePersonIds($album, $user);

		/** @var PhotoBuilder<Photo> $ids_query */
		$ids_query = Photo::query()->select('photos.id');

		if ($this->config_manager->getValueAsBool('PA_override_visibility')) {
			$this->photo_query_policy
				->applySensitivityFilter(
					query: $ids_query,
					user: $user,
					origin: null,
					include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_person_albums')
				)
				->where(fn (Builder $q) => $this->getPhotoIdsWithPersons($q, $person_ids, $album->is_and));
		} else {
			$this->photo_query_policy
				->applySearchabilityFilter(
					query: $ids_query,
					user: $user,
					unlocked_album_ids: $unlocked_album_ids,
					origin: null,
					include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_person_albums')
				)
				->where(fn (Builder $q) => $this->getPhotoIdsWithPersons($q, $person_ids, $album->is_and));
		}

		return $ids_query;
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
}
