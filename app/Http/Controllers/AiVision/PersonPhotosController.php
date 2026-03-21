<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\ListPersonsRequest;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for listing photos containing faces of a given Person.
 *
 * GET /Person/{id}/photos
 */
class PersonPhotosController extends Controller
{
	/**
	 * Return paginated photos that contain at least one face for the given person.
	 * Applies access control:
	 * - Admin: all photos
	 * - Authenticated user: photos they own + photos in public albums
	 * - Guest: photos in public albums only.
	 *
	 * @return \Illuminate\Pagination\LengthAwarePaginator<\stdClass>
	 */
	public function index(ListPersonsRequest $_request, string $id)
	{
		$user = Auth::user();
		$person = Person::findOrFail($id);

		// Non-admin cannot see non-searchable persons unless linked to them
		if (!($user?->may_administrate === true) && !$person->is_searchable && $person->user_id !== $user?->id) {
			abort(403);
		}

		$query = Photo::query()
			->select(['photos.id', 'photos.title'])
			->join('faces', 'faces.photo_id', '=', 'photos.id')
			->where('faces.person_id', '=', $id)
			->orderBy('photos.taken_at', 'desc');

		// Access control: restrict to photos accessible to the current user
		if (!($user?->may_administrate === true)) {
			$query->where(function ($q) use ($user): void {
				// Photos in at least one public album (base_albums.is_public = true)
				$q->whereExists(function ($sub): void {
					$sub->select(\Illuminate\Support\Facades\DB::raw(1))
						->from('photo_album')
						->join('base_albums', 'base_albums.id', '=', 'photo_album.album_id')
						->whereColumn('photo_album.photo_id', 'photos.id')
						->where('base_albums.is_public', '=', true);
				});

				// OR photos owned by the authenticated user
				if ($user !== null) {
					$q->orWhere('photos.owner_id', '=', $user->id);
				}
			});
		}

		return $query->distinct()->paginate(50);
	}
}
