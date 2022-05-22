<?php

namespace App\Actions;

use App\Actions\Photo\Archive;
use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthorizedException;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\JoinClause;

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
	 *    (cp. {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}).
	 *  - the photo is public
	 *
	 * @param FixedQueryBuilder $query
	 *
	 * @return FixedQueryBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function applyVisibilityFilter(FixedQueryBuilder $query): FixedQueryBuilder
	{
		$this->prepareModelQueryOrFail($query, false, true, true);

		if (AccessControl::is_admin()) {
			return $query;
		}

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibilitySubQuery = function (FixedQueryBuilder $query2) use ($userID) {
			$this->albumAuthorisationProvider->appendAccessibilityConditions($query2->getQuery());
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
	 * @param Photo|null $photo the photo; `null` is accepted for convenience
	 *                          and the `null` photo is always authorized
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	public function isVisible(?Photo $photo): bool
	{
		return
			$photo === null ||
			AccessControl::is_current_user_or_admin($photo->owner_id) ||
			$photo->is_public ||
			$this->albumAuthorisationProvider->isAccessible($photo->album);
	}

	/**
	 * Checks whether the photo may be downloaded by the current user.
	 *
	 * Previously, this code was part of {@link Archive::extractFileInfo()}.
	 * In particular, the method threw to {@link UnauthorizedException} with
	 * custom error messages:
	 *
	 *  - `'User is not allowed to download the image'`, if the user was not
	 *    the owner, the user was allowed to see the photo (i.e. the album
	 *    is shared with the user), but the album does not allow to dowload
	 *    photos
	 *  - `'Permission to download is disabled by configuration'`, if the
	 *    user was not the owner, the photo was not part of any album (i.e.
	 *    unsorted), the photo was public and downloading was disabled by
	 *    configuration.
	 *
	 * TODO: Check if these custom error messages are still needed. If yes, consider not to return a boolean value but rename the method to `assert...` and throw exceptions with custom error messages.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function isDownloadable(Photo $photo): bool
	{
		if (!$this->isVisible($photo)) {
			return false;
		}

		return
			AccessControl::is_current_user_or_admin($photo->owner_id) ||
			$photo->album?->is_downloadable ||
			($photo->album === null && Configs::get_value('downloadable', '0') === '1');
	}

	/**
	 * Restricts a photo query to _searchable_ photos.
	 *
	 * A photo is _searchable_ if at least one of the following conditions
	 * hold:
	 *
	 *  - the photo is part of an album which the user is allowed to browse
	 *  - the user is the owner of the photo
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
	 * @param FixedQueryBuilder $query  the photo query which shall be restricted
	 * @param Album|null        $origin the optional top album which is used as a search base
	 *
	 * @return FixedQueryBuilder the restricted photo query
	 *
	 * @throws InternalLycheeException
	 */
	public function applySearchabilityFilter(FixedQueryBuilder $query, ?Album $origin = null): FixedQueryBuilder
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
					$query->getQuery(),
					$origin?->_lft,
					$origin?->_rgt
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
	 * for a definition of a searchable photo.
	 *
	 * Moreover, the raw clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param BaseBuilder     $query       the photo query which shall be
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
	 * @return BaseBuilder the restricted photo query
	 *
	 * @throws QueryBuilderException
	 */
	public function appendSearchabilityConditions(BaseBuilder $query, int|string|null $originLeft, int|string|null $originRight): BaseBuilder
	{
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
		$maySearchPublic = Configs::get_value('public_photos_hidden', '1') !== '1';

		try {
			// there must be no unreachable album between the origin and the photo
			$query->whereNotExists(function (BaseBuilder $q) use ($originLeft, $originRight) {
				$this->albumAuthorisationProvider->appendUnreachableAlbumsCondition($q, $originLeft, $originRight);
			});

			// Special care needs to be taken for unsorted photo, i.e. photos on
			// the root level:
			// The condition for "no unreachable albums along the path" fails for
			// root album due to two reasons:
			//   a) the path of albums between to the root album is empty; hence,
			//      there are never any unreachable albums in between
			//   b) while all users (even unauthenticated users) may access the
			//      root album, they must only see their own photos or public
			//      photos (this is different to any other album: if users are
			//      allowed to access an album, they may also see its content)
			$query->whereNotNull('photos.album_id');

			if ($maySearchPublic) {
				$query->orWhere('photos.is_public', '=', true);
			}
			if ($userID !== null) {
				$query->orWhere('photos.owner_id', '=', $userID);
			}
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}

		return $query;
	}

	/**
	 * Checks whether the photo is editable by the current user.
	 *
	 * A photo is called _editable_ if the current user is allowed to edit
	 * the photo's properties.
	 * A photo is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the photo
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function isEditable(Photo $photo): bool
	{
		return AccessControl::is_current_user_or_admin($photo->owner_id);
	}

	/**
	 * Checks whether the designated photos are editable by the current user.
	 *
	 * See {@link PhotoAuthorisationProvider::isEditable()} for the definition
	 * when a photo is editable.
	 *
	 * This method is mostly only useful during deletion of photos, when no
	 * photo models are loaded for efficiency reasons.
	 * If a photo model is required anyway (because it shall be edited),
	 * then first load the photo once and use
	 * {@link PhotoAuthorisationProvider::isEditable()}
	 * instead in order to avoid several DB requests.
	 *
	 * @param string[] $photoIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function areEditableByIDs(array $photoIDs): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}
		if (!AccessControl::is_logged_in()) {
			return false;
		}

		$user = AccessControl::user();

		// Make IDs unique as otherwise count will fail.
		$photoIDs = array_unique($photoIDs);

		return
			count($photoIDs) === 0 ||
			Photo::query()
				->whereIn('id', $photoIDs)
				->where('owner_id', $user->id)
				->count() === count($photoIDs);
	}

	/**
	 * Throws an exception if the given query does not query for a photo.
	 *
	 * @param FixedQueryBuilder $query         the query to prepare
	 * @param bool              $addAlbums     if true, joins photo query with (parent) albums
	 * @param bool              $addBaseAlbums if true, joins photos query with (parent) base albums
	 * @param bool              $addShares     if true, joins photo query with user share table of (parent) album
	 *
	 * @throws InternalLycheeException
	 */
	private function prepareModelQueryOrFail(FixedQueryBuilder $query, bool $addAlbums, bool $addBaseAlbums, bool $addShares): void
	{
		$model = $query->getModel();
		$table = $query->getQuery()->from;
		if (!($model instanceof Photo && $table === 'photos')) {
			throw new InvalidQueryModelException('photo');
		}

		// We must only add the share, i.e. left join with `user_base_album`,
		// if and only if we restrict the eventual query to the ID of the
		// authenticated user by a `WHERE`-clause.
		// If we were doing a left join unconditionally, then some
		// photos might appear multiple times as part of the result
		// because the parent album of a photo might be shared with more than
		// one user.
		// Hence, we must restrict the `LEFT JOIN` to the user ID which
		// is also used in the outer `WHERE`-clause.
		// See `applyVisibilityFilter`.
		$addShares = $addShares && AccessControl::is_logged_in();

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
			$userID = AccessControl::id();
			$query->leftJoin('user_base_album',
				function (JoinClause $join) use ($userID) {
					$join
						->on('user_base_album.base_album_id', '=', 'base_albums.id')
						->where('user_base_album.user_id', '=', $userID);
				}
			);
		}
	}
}
