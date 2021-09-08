<?php

namespace App\Actions\Search;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PhotoSearch
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
	}

	/**
	 * Restricts a photo query to _visible_ photos.
	 *
	 * A photo is called _visible_ if the current user is allowed to see the
	 * photo.
	 * A photo is _visible_ if any of the following conditions hold
	 * (OR-clause):
	 *
	 *  - the user is the admin
	 *  - the user is the owner of the photo
	 *  - the photo is part of an album which the user is allowed to access
	 *  - the photo is unsorted (not part of any album) and the user is granted the right to upload photos
	 *  - the photo is public and public photos are not excluded from search results
	 *
	 * TODO: Move this method into a `PhotoAuthorizationProvider` in the same spirit as `AlbumAuthorizationProvider`.
	 *
	 * TODO: This method is a duplicate of {@link \App\Relations\HasManyPhotos::applyVisibilityFilter()}.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	private function applyVisibilityFilter(Builder $query): Builder
	{
		if (AccessControl::is_admin()) {
			return $query;
		}

		if (!AccessControl::is_logged_in()) {
			// We must wrap everything into an outer query to avoid any undesired
			// effects in case that the original query already contains an
			// "OR"-clause.
			return $query->where(
				function (Builder $query2) {
					$query2->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q));
					if (Configs::get_value('public_photos_hidden', '1') === '0') {
						$query2->orWhere('public', '=', true);
					}
				}
			);
		}

		$userID = AccessControl::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		return $query->where(
			function (Builder $query2) use ($userID) {
				$query2->where('owner_id', '=', $userID);
				$query2->orWhereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q));
				if (AccessControl::can_upload()) {
					$query2->orWhereNull('album_id');
				}
				if (Configs::get_value('public_photos_hidden', '1') === '0') {
					$query2->orWhere('public', '=', true);
				}
			}
		);
	}

	public function query(array $terms): Collection
	{
		$query = $this->applyVisibilityFilter(
			Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
		);

		foreach ($terms as $term) {
			$query->where(
				fn (Builder $query) => $query
					->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
					->orWhere('tags', 'like', '%' . $term . '%')
					->orWhere('location', 'like', '%' . $term . '%')
					->orWhere('model', 'like', '%' . $term . '%')
					->orWhere('taken_at', 'like', '%' . $term . '%')
			);
		}

		return $query->get();
	}
}
