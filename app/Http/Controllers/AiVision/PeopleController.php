<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\DestroyPersonRequest;
use App\Http\Requests\Person\ListPersonsRequest;
use App\Http\Requests\Person\ShowPersonRequest;
use App\Http\Requests\Person\StorePersonRequest;
use App\Http\Requests\Person\UpdatePersonRequest;
use App\Http\Resources\Models\PersonResource;
use App\Models\Person;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * CRUD controller for Person records.
 */
class PeopleController extends Controller
{
	/**
	 * List persons (paginated).
	 * Filters non-searchable persons for non-admin users who are not linked to the person.
	 *
	 * @return PaginatedDataCollection<(int|string),PersonResource>
	 */
	public function index(ListPersonsRequest $_request): PaginatedDataCollection
	{
		$user = Auth::user();
		$query = Person::query()->orderBy('name');

		if ($user === null || !$user->may_administrate) {
			// Non-admin: only show searchable persons, plus the person linked to the current user
			$user_id = $user?->id;
			$query->where(function ($q) use ($user_id): void {
				$q->where('is_searchable', '=', true);
				if ($user_id !== null) {
					$q->orWhere('user_id', '=', $user_id);
				}
			});
		}

		$persons = $query->paginate(50);

		return PersonResource::collect($persons, PaginatedDataCollection::class);
	}

	/**
	 * Show a single person.
	 *
	 * @return PersonResource
	 */
	public function show(ShowPersonRequest $request): PersonResource
	{
		return PersonResource::fromModel($request->person());
	}

	/**
	 * Create a new Person.
	 *
	 * @return PersonResource
	 */
	public function store(StorePersonRequest $request): PersonResource
	{
		$is_searchable_default = app(ConfigManager::class)->getValueAsString('ai_vision_face_person_is_searchable_default') === '1';

		$person = new Person();
		$person->name = $request->name();
		$person->user_id = $request->userId();
		$person->is_searchable = $is_searchable_default;
		$person->save();

		return PersonResource::fromModel($person);
	}

	/**
	 * Update a Person (name and/or searchability).
	 *
	 * @return PersonResource
	 */
	public function update(UpdatePersonRequest $request): PersonResource
	{
		$person = $request->person();

		if ($request->isSearchable() !== null) {
			$person->is_searchable = $request->isSearchable();
		}

		if ($request->name() !== null) {
			$person->name = $request->name();
		}

		$person->save();

		return PersonResource::fromModel($person);
	}

	/**
	 * Delete a Person. All associated faces will have their person_id set to null.
	 */
	public function destroy(DestroyPersonRequest $request): void
	{
		$person = Person::findOrFail($request->personId());
		$person->delete();
	}
}
