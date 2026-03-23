<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\ListPersonsRequest;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
	 * Returns the standard Laravel paginator JSON shape: {data, links, meta}.
	 *
	 * @return LengthAwarePaginator<PhotoResource>
	 */
	public function index(ListPersonsRequest $_request, string $id): LengthAwarePaginator
	{
		$user = Auth::user();
		$person = Person::findOrFail($id);

		// Non-admin cannot see non-searchable persons unless linked to them
		if (!($user?->may_administrate === true) && !$person->is_searchable && $person->user_id !== $user?->id) {
			abort(403);
		}

		$query = Photo::query()
			->select('photos.*')
			->join('faces', 'faces.photo_id', '=', 'photos.id')
			->where('faces.person_id', '=', $id)
			->with(['size_variants', 'tags', 'palette', 'statistics', 'rating',
				'faces.person', 'faces.suggestions.suggestedFace.person'])
			->orderBy('photos.taken_at', 'desc');

		// Access control: restrict to photos accessible to the current user
		if (!($user?->may_administrate === true)) {
			$query->where(function ($q) use ($user): void {
				// Photos in at least one public album (base_albums.is_public = true)
				$q->whereExists(function ($sub): void {
					$sub->select(DB::raw(1))
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

		/** @var LengthAwarePaginator<PhotoResource> */
		return $query->distinct()->paginate(50)->through(
			fn (Photo $photo) => new PhotoResource(
				photo: $photo,
				album_id: null,
				should_downgrade_size_variants: false,
			)
		);
	}
}
