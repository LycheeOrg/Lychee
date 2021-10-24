<?php

namespace App\Actions;

use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;

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
		$this->prepareModelQueryOrFail($query, false, true, true);

		if (AccessControl::is_admin()) {
			return $query;
		}

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibilitySubQuery = function (Builder $query2) use ($userID) {
			$this->albumAuthorisationProvider->appendAccessibilityConditions($query2);
			$query2->orWhere('photos.is_public', '=', true);
			if ($userID !== null) {
				$query2->orWhere('photos.owner_id', '=', $userID);
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
				Photo::query()->where('photos.id', '=', $photoID)
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
		$this->prepareModelQueryOrFail($query, true, false, false);

		// If origin is set, also restrict the search result for admin
		// to photos which are in albums below origin.
		// This is not a security filter, but simply functional.
		if ($origin) {
			$query
				->where('albums._lft', '>=', $origin->_lft)
				->where('albums._rgt', '<=', $origin->_rgt);
		}

		if (AccessControl::is_admin()) {
			return $query;
		} else {
			return $query->where(function (Builder $query) use ($origin) {
				$this->appendSearchabilityConditions(
					$query,
					$origin ? $origin->_lft : null,
					$origin ? $origin->_rgt : null
				);
			});
		}
	}

	/**
	 * Adds the conditions of _searchable_ photos to the query.
	 *
	 * **Attention:** This method is only meant for internal use.
	 * Use {@link PhotoAuthorisationProvider::applySearchabilityFilter()}
	 * if called from other places instead.
	 *
	 * This method adds the WHERE conditions without any further pre-cautions.
	 * The method silently assumes that the SELECT clause contains the tables
	 *
	 *  - **`albums`**.
	 *
	 * See {@link AlbumAuthorisationProvider::applySearchabilityFilter()}
	 * for a definition of a searchable photos.
	 *
	 * Moreover, the raw clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param Builder         $query       the photo query which shall be
	 *                                     restricted
	 * @param int|string|null $originLeft  optionally constraints the search
	 *                                     base; an integer value is
	 *                                     interpreted a raw left bound of the
	 *                                     search base; a string value is
	 *                                     interpreted as a reference to a
	 *                                     column which shall be used as a
	 *                                     left bound
	 * @param int|string|null $originRight like `$originLeft` but for the
	 *                                     right bound
	 *
	 * @return Builder the restricted photo query
	 */
	public function appendSearchabilityConditions(Builder $query, $originLeft, $originRight): Builder
	{
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
		$maySearchPublic = Configs::get_value('public_photos_hidden', '1') !== '1';

		$query->whereNotExists(function (BaseBuilder $q) use ($originLeft, $originRight) {
			$this->albumAuthorisationProvider->appendBlockedAlbumsCondition($q, $originLeft, $originRight);
		});
		if ($maySearchPublic) {
			$query->orWhere('photos.is_public', '=', true);
		}
		if ($userID !== null) {
			$query->orWhere('photos.owner_id', '=', $userID);
		}

		return $query;
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
				->whereIn('photos.id', $photoIDs)
				->where('photos.owner_id', '=', $userID)
				->count() === count($photoIDs);
		}

		return true;
	}

	/**
	 * Throws an exception if the given query does not query for a photo.
	 *
	 * @param Builder $query         the query to prepare
	 * @param bool    $addAlbums     if true, joins photo query with (parent) albums
	 * @param bool    $addBaseAlbums if true, joins photos query with (parent) base albums
	 * @param bool    $addShares     if true, joins photo query with user share table of (parent) album
	 */
	private function prepareModelQueryOrFail(Builder $query, bool $addAlbums, bool $addBaseAlbums, bool $addShares): void
	{
		$model = $query->getModel();
		$table = $query->getQuery()->from;
		if (!($model instanceof Photo && $table === 'photos')) {
			throw new \InvalidArgumentException('the given query does not query for photos');
		}
		// Ensure that only columns of the photos are selected,
		// if no specific columns are yet set.
		// Otherwise, we cannot add a JOIN clause below
		// without accidentally adding all columns of the join, too.
		if (empty($query->columns)) {
			$query->select(['photos.*']);
		}
		if ($addAlbums) {
			$query->leftJoin('albums', 'albums.id', '=', 'photos.album_id');
		}
		if ($addBaseAlbums) {
			$query->leftJoin('base_albums', 'base_albums.id', '=', 'photos.album_id');
		}
		if ($addShares) {
			$query->leftJoin('user_base_album', 'user_base_album.base_album_id', '=', 'photos.album_id');
		}
	}
}
