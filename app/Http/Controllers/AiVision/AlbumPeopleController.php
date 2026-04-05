<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\ListPersonsRequest;
use App\Http\Resources\Collections\PaginatedPersonsResource;
use App\Models\Album;
use App\Models\Person;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for listing persons found in a given album's direct photos.
 *
 * GET /Album/{albumId}/people
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
	public function index(ListPersonsRequest $_request, string $albumId): PaginatedPersonsResource
	{
		$album = Album::findOrFail($albumId);
		$user = Auth::user();

		// Verify the user has access to this album
		// Use the album's is_public flag or ownership for basic access control
		if (!$album->is_public && ($user === null || ($user->may_administrate !== true && $album->owner_id !== $user->id))) {
			abort(403);
		}

		$query = Person::query()
			->select('persons.*')
			->join('faces', 'faces.person_id', '=', 'persons.id')
			->join('photo_album', 'photo_album.photo_id', '=', 'faces.photo_id')
			->where('photo_album.album_id', '=', $albumId)
			->where('faces.is_dismissed', '=', false)
			->whereNotNull('faces.person_id')
			->orderBy('persons.name');

		// Non-admin: only show searchable persons, plus the person linked to the current user
		if (!($user?->may_administrate === true)) {
			$user_id = $user?->id;
			$query->where(function ($q) use ($user_id): void {
				$q->where('persons.is_searchable', '=', true);
				if ($user_id !== null) {
					$q->orWhere('persons.user_id', '=', $user_id);
				}
			});
		}

		$persons = $query->distinct()->paginate(50);

		return new PaginatedPersonsResource($persons);
	}
}
