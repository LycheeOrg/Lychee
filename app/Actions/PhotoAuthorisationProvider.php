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
	 *    (cp. {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}.
	 *  - the photo is unsorted (not part of any album) and the user is granted
	 *    the right to upload photos
	 *  - the photo is public
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function applyVisibilityFilter(Builder $query): Builder
	{
		if (AccessControl::is_admin()) {
			return $query;
		}

		$this->failForWrongQueryModel($query);
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibilitySubQuery = function (Builder $query2) use ($userID) {
			$query2
				->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q))
				->orWhere('is_public', '=', true);
			if ($userID !== null) {
				$query2->orWhere('owner_id', '=', $userID);
			}
		};

		return $query->where($visibilitySubQuery);
	}

	/**
	 * Checks whether the photo is visible by the current user.
	 *
	 * See {@link PhotoAuthorisationProvider::applyVisibilityFilter()} for a
	 * specification of the rules when a photo is visible.
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

		// We use `applyVisibilityFilter` to build a query, but don't hydrate
		// a model
		return $this->applyVisibilityFilter(
				Photo::query()->where('id', '=', $photoID)
			)->count() !== 0;
	}

	/**
	 * Restricts a photo query to _searchable_ photos.
	 *
	 * A photo is _searchable_ if at least one of the following conditions
	 * hold:
	 *
	 *  - it is member of a _browsable_ album, or
	 *  - the currently authenticated user is allowed to upload photos and the photo is unsorted
	 *  - the photo is public and searching through public photos is enabled
	 *
	 * See {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
	 * for a definition of a browsable album.
	 *
	 * The search result is restricted to photos in albums which are below
	 * `$origin`.
	 *
	 * **Attention**:
	 * For efficiency reasons this method does not check if `$origin` itself
	 * is accessible.
	 * The method simply assumes that the user has already legitimately
	 * accessed the origin album, if the caller provides an album model.
	 *
	 * @param Builder    $query  the photo query which shall be restricted
	 * @param Album|null $origin the optional top album which is used as a search base
	 *
	 * @return Builder the restricted photo query
	 */
	public function applySearchabilityFilter(Builder $query, ?Album $origin = null): Builder
	{
		$this->failForWrongQueryModel($query);

		if (AccessControl::is_admin()) {
			// If origin is set, also restrict the search result for admin
			// to photos which are in albums below origin.
			// This is not a security filter, but simply functional.
			// Note: For non-admin user (see below) this condition is part of
			// `applyBrowsabilityFilter`.
			// Technically, we could use `applyBrowsabilityFilter` here, too,
			// but we do not for performance reasons.
			// As we know that an admin can browse every album, we do not need
			// the complexity of `applyBrowsabilityFilter`.
			if ($origin) {
				$query->whereHas('album', function (Builder $q) use ($origin) {
					$q->where('_lft', '>=', $origin->_lft)
						->where('_rgt', '<=', $origin->_rgt);
				});
			}

			return $query;
		}

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
		$maySearchPublic = Configs::get_value('public_photos_hidden', '1') !== '1';

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$searchabilitySubQuery = function (Builder $query2) use ($userID, $maySearchPublic, $origin) {
			$query2
				// The following line might let the runtime explode.
				// If the query planner of the DBMS is really bad, then the
				// browsability filter is invoked for every album of every
				// photo of the result, even if many photos are in the same
				// album.
				// This is a drawback of `->whereHas` which compiles into
				// a `WHERE EXISTS (subquery)` clause.
				// TODO: Rewrite to `->whereDoesntHave` and a a filter function which returns blocked albums, if this query turns out to be too slow
				->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyBrowsabilityFilter($q, $origin));
			if ($maySearchPublic) {
				$query2->orWhere('is_public', '=', true);
			}
			if ($userID !== null) {
				$query2->orWhere('owner_id', '=', $userID);
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

	/**
	 * Throws an exception if the given query does not query for a photo.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @param Builder $query
	 */
	private function failForWrongQueryModel(Builder $query): void
	{
		$model = $query->getModel();
		if (!($model instanceof Photo)) {
			throw new \InvalidArgumentException('the given query does not query for photos');
		}
	}
}
