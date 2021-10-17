<?php

namespace App\Actions;

use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PhotoAuthorisationProvider
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
	}

	/**
	 * Checks whether the photo is visible by the current user.
	 *
	 * A photo is called _visible_ if the current user is allowed to see the
	 * photo.
	 * A photo is _visible_ if any of the following conditions hold
	 * (OR-clause):
	 *
	 *  - the user is the admin
	 *  - the user is the owner of the photo
	 *  - the photo is part of an album which the user is allowed to access
	 *    (cp. {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}.
	 *  - the photo is unsorted (not part of any album) and the user is granted
	 *    the right to upload photos
	 *  - the photo is public
	 *
	 * @param int $photoID
	 *
	 * @return bool
	 */
	public function isVisible(int $photoID): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
		$maySeeUnsorted = AccessControl::can_upload();

		$visibilitySubQuery = function (Builder $query2) use ($userID, $maySeeUnsorted) {
			$query2
				->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q))
				->orWhere('is_public', '=', true);
			if ($userID !== null) {
				$query2->orWhere('owner_id', '=', $userID);
			}
			if ($maySeeUnsorted) {
				$query2->orWhereNull('album_id');
			}
		};

		// If we don't have an instance of a model, then use
		// `applyVisibilityFilter` to build a query, but don't hydrate a
		// model
		return Photo::query()
			->where('id', '=', $photoID)
			->where($visibilitySubQuery)
			->count() !== 0;
	}

	public function applySearchabilityFilter(Builder $query, ?Album $origin = null): Builder
	{
		if (!($query->getModel() instanceof Photo)) {
			throw new \InvalidArgumentException('the given query does not query for photos');
		}

		if (AccessControl::is_admin()) {
			return $query->whereHas('album',
				fn (Builder $q) => $this->albumAuthorisationProvider->applyBrowsabilityFilter($q, $origin)
			);
		}

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
		$arePublicPhotosSearchable = Configs::get_value('public_photos_hidden', '1') === '1';
		$maySeeUnsorted = AccessControl::can_upload();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$searchabilitySubQuery = function (Builder $query2) use ($userID, $arePublicPhotosSearchable, $maySeeUnsorted, $origin) {
			$query2
				// The following line might let the runtime explode.
				// The browsability filter is invoked for every album of every
				// photo of the result, even if many photos are in the same
				// album.
				// This is a drawback of `->whereHas` which compiles into
				// a `WHERE EXISTS (subquery)` clause.
				// TODO: Rewrite to `->whereDoesntHave` and a a filter function which returns blocked albums
				->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyBrowsabilityFilter($q, $origin));
			if ($arePublicPhotosSearchable) {
				$query2->orWhere('is_public', '=', true);
			}
			if ($userID !== null) {
				$query2->orWhere('owner_id', '=', $userID);
			}
			if ($maySeeUnsorted) {
				$query2->orWhereNull('album_id');
			}
		};

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		return $query->where($searchabilitySubQuery);
	}

	/**
	 * Checks whether the photos with the given IDs are editable by the
	 * current user.
	 *
	 * A photo is called _editable_ if the current user is allowed to edit
	 * the photo's properties.
	 * A photo is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the photo
	 *
	 * @param int[] $photoIDs
	 *
	 * @return bool
	 */
	public function areEditable(array $photoIDs): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}
		if (!AccessControl::is_logged_in()) {
			return false;
		}

		$userID = AccessControl::id();
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$photoIDs = array_unique($photoIDs);
		if (count($photoIDs) > 0) {
			return Photo::query()
				->whereIn('id', $photoIDs)
				->where('owner_id', '=', $userID)
				->count() === count($photoIDs);
		}

		return true;
	}
}
