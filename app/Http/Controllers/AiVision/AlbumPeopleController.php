<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\GetAlbumPersonsRequest;
use App\Http\Resources\Collections\PaginatedPersonsResource;
use App\Models\Album;
use App\Models\Person;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for listing persons found in a given album's direct photos.
 *
 * GET /Album/{album_id}/people
 *
 * Returns persons distinct to the album's photos (non-recursive — direct photos only via photo_album pivot).
 * Respects ai_vision_face_permission_mode and is_searchable filtering.
 */
class AlbumPeopleController extends Controller
{
	/**
	 * Return paginated list of distinct persons found in the given album's direct photos.
	 *
	 * @return PaginatedPersonsResource
	 */
	public function index(GetAlbumPersonsRequest $request): PaginatedPersonsResource
	{
		/** @var Album $album */
		$album = $request->album();
		$user = Auth::user();

		$query = Person::query()
			->whereHas('faces', fn ($q) => $q
				->where('is_dismissed', false)
				->whereHas('photo', fn ($qp) => $qp
					->whereHas('albums', fn ($qa) => $qa->where('albums.id', $album->id))
				)
			)
			->orderBy('name');

		// Non-admin: only show searchable persons, plus the person linked to the current user
		if ($user?->may_administrate !== true) {
			$query->searchable($user?->id);
		}

		$persons = $query->paginate(50);

		return new PaginatedPersonsResource($persons);
	}
}
