<?php

namespace App\Actions;

use App\Contracts\AbstractAlbum;
use App\Contracts\BaseAlbum;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

/**
 * Class AlbumAuthorisationProvider.
 */
class AlbumAuthorisationProvider
{
	const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';
	protected AlbumFactory $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

	/**
	 * Restricts an album query to _visible_ albums.
	 *
	 * An album is called _visible_ if the current user is allowed to see the
	 * album (itself) within a listing or similar.
	 * An album is _visible_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the album
	 *  - the album is shared with the user and the album does not require a direct link
	 *  - the album is public and the album does not require a direct link
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function applyVisibilityFilter(Builder $query): Builder
	{
		$this->failForWrongQueryModel($query);
		$model = $query->getModel();

		if (AccessControl::is_admin()) {
			return $query;
		}

		if (!AccessControl::is_logged_in()) {
			// We must wrap everything into an outer query to avoid any undesired
			// effects in case that the original query already contains an
			// "OR"-clause.
			// The sub-query only uses properties (i.e. columns) which are
			// defined on the common base model for all albums.
			$visibilitySubQuery = function (Builder $query2) {
				$query2
					->where('requires_link', '=', false)
					->where('is_public', '=', true);
			};

			if ($model instanceof BaseAlbumImpl) {
				// If the queried model is the base class, we can directly
				// apply the sub-query
				return $query->where($visibilitySubQuery);
			} else {
				// If the queried model is not the base class, but a derived one,
				// we must apply the sub-query to the relation.
				return $query->whereHas('base_class', $visibilitySubQuery);
			}
		}

		$userID = AccessControl::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibilitySubQuery = function (Builder $query2) use ($userID) {
			$query2
				->where('owner_id', '=', $userID)
				->orWhere(fn (Builder $q) => $q
					->where('requires_link', '=', false)
					->whereHas(
						'shared_with',
						fn (Builder $q2) => $q2->where('user_id', '=', $userID)
					)
				)
				->orWhere(fn (Builder $q) => $q
					->where('requires_link', '=', false)
					->where('is_public', '=', true)
				);
		};

		if ($model instanceof BaseAlbumImpl) {
			// If the queried model is the base class, we can directly
			// apply the sub-query
			return $query->where($visibilitySubQuery);
		} else {
			// If the queried model is not the base class, but a derived one,
			// we must apply the sub-query to the relation.
			return $query->whereHas('base_class', $visibilitySubQuery);
		}
	}

	/**
	 * Checks whether the album with the given ID is visible for the current
	 * user.
	 *
	 * For real albums (i.e. albums that are stored in the DB), see
	 * {@link AlbumAuthorisationProvider::applyVisibilityFilter()} for a
	 * specification of the rules when an album is visible.
	 * In other cases, the following holds:
	 *  - the root album is visible if and only if the user is authenticated
	 *  - the built-in smart albums are visible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is the album of recent photos and public by configuration, or
	 *     - the album is the album of starred photos and public by configuration
	 *
	 * Note, this method tries to minimize DB queries and any overhead due
	 * to hydration of models.
	 * If an actual instance of a {@link AbstractAlbum} model is passed in,
	 * then the DB won't be queried at all, because all checks are performed
	 * on the values of the already hydrated model.
	 * If an ID is passed, then the method runs a very efficient COUNT
	 * query on the DB.
	 * In particular, no {@link Album} nor {@link TagAlbum} model is hydrated
	 * to avoid any overhead.
	 *
	 * Tips for usage:
	 *  - If you already have a {@link AbstractAlbum} instance, pass that.
	 *    This is most efficient.
	 *  - If you do not have a {@link AbstractAlbum} instance, but you will
	 *    need one later anyway, then use {@link AlbumFactory} to first fetch
	 *    the album from DB and pass the album.
	 *    This avoids a second DB query later.
	 *  - If you do not have a {@link AbstractAlbum} instance, and you won't
	 *    need one later, simply pass the ID of the album.
	 *    This avoids the overhead of model hydration.
	 *
	 * @param string|int|AbstractAlbum|null $albumModelOrID
	 *
	 * @return bool
	 */
	public function isVisible($albumModelOrID): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}

		// deal with root album first
		if (empty($albumModelOrID)) {
			return AccessControl::is_logged_in();
		}

		/** @var ?AbstractAlbum $album */
		/** @var int|string $albumID */
		list($albumID, $album) = $this->disassembleAlbumParameter($albumModelOrID);

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum($albumID);
		}

		// Deal with albums that are actual models.
		// If we already have an instance of a model, then avoid an unnecessary
		// DB query.
		// We perform the visibility checks directly on the album model.
		// The semantics of these checks must be kept in sync with the
		// checks in `applyVisibilityFilter`.
		if ($album) {
			/* @var BaseAlbum $album */

			if (!AccessControl::is_logged_in()) {
				return !$album->requires_link && $album->is_public;
			} else {
				$userID = AccessControl::id();

				return
					($album->owner_id === $userID) ||
					(!$album->requires_link && $album->shared_with()->where('user_id', '=', $userID)->count()) ||
					(!$album->requires_link && $album->is_public);
			}
		} else {
			// If we don't have an instance of a model, then use
			// `applyVisibilityFilter` to build a query, but don't hydrate a
			// model
			return $this->applyVisibilityFilter(
				BaseAlbumImpl::query()->where('id', '=', intval($albumID))
			)->count() !== 0;
		}
	}

	/**
	 * Restricts an album query to _accessible_ albums.
	 *
	 * An album is called _accessible_ if the current user is allowed to
	 * browse into it, i.e. if the current user may open it and see its
	 * content.
	 * An album is _accessible_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the album
	 *  - the album is shared with the user
	 *  - the album is public AND no password is set
	 *  - the album is public AND has been unlocked
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function applyAccessibilityFilter(Builder $query): Builder
	{
		$this->failForWrongQueryModel($query);
		$model = $query->getModel();

		if (AccessControl::is_admin()) {
			return $query;
		}

		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();

		if (!AccessControl::is_logged_in()) {
			// We must wrap everything into an inner query to avoid any undesired
			// effects in case that the original query already contains an
			// "OR"-clause.
			// The sub-query only uses properties (i.e. columns) which are
			// defined on the common base model for all albums.
			$accessibilitySubQuery = function (Builder $query2) use ($unlockedAlbumIDs) {
				$query2
					->where(fn (Builder $q) => $q
						->where('is_public', '=', true)
						->whereNull('password')
					)
					->orWhere(fn (Builder $q) => $q
						->where('is_public', '=', true)
						->whereIn('id', $unlockedAlbumIDs)
					);
			};

			if ($model instanceof BaseAlbumImpl) {
				// If the queried model is the base class, we can directly
				// apply the sub-query
				return $query->where($accessibilitySubQuery);
			} else {
				// If the queried model is not the base class, but a derived one,
				// we must apply the sub-query to the relation.
				return $query->whereHas('base_class', $accessibilitySubQuery);
			}
		}

		$userID = AccessControl::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$accessibilitySubQuery = function (Builder $query2) use ($unlockedAlbumIDs, $userID) {
			$query2
				->where('owner_id', '=', $userID)
				->orWhereHas(
					'shared_with',
					fn (Builder $q) => $q->where('user_id', '=', $userID)
				)
				->orWhere(fn (Builder $q) => $q
					->where('is_public', '=', true)
					->whereNull('password')
				)
				->orWhere(fn (Builder $q) => $q
					->where('is_public', '=', true)
					->whereIn('id', $unlockedAlbumIDs)
				);
		};

		if ($model instanceof BaseAlbumImpl) {
			// If the queried model is the base class, we can directly
			// apply the sub-query
			return $query->where($accessibilitySubQuery);
		} else {
			// If the queried model is not the base class, but a derived one,
			// we must apply the sub-query to the relation.
			return $query->whereHas('base_class', $accessibilitySubQuery);
		}
	}

	/**
	 * Checks whether the album is accessible by the current user.
	 *
	 * For real albums (i.e. albums that are stored in the DB), see
	 * {@link AlbumAuthorisationProvider::applyAccessibilityFilter()} for a
	 * specification of the rules when an album is accessible.
	 * In other cases, the following holds:
	 *  - the root album is accessible if and only if the user is authenticated
	 *  - the built-in smart albums are accessible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is the album of recent photos and public by configuration, or
	 *     - the album is the album of starred photos and public by configuration
	 *
	 * Note, this method tries to minimize DB queries and any overhead due
	 * to hydration of models.
	 * If an actual instance of a {@link AbstractAlbum} model is passed in,
	 * then the DB won't be queried at all, because all checks are performed
	 * on the values of the already hydrated model.
	 * If an ID is passed, then the method runs a very efficient COUNT
	 * query on the DB.
	 * In particular, no {@link Album} nor {@link TagAlbum} model is hydrated
	 * to avoid any overhead.
	 *
	 * Tips for usage:
	 *  - If you already have a {@link AbstractAlbum} instance, pass that.
	 *    This is most efficient.
	 *  - If you do not have a {@link AbstractAlbum} instance, but you will
	 *    need one later anyway, then use {@link AlbumFactory} to first fetch
	 *    the album from DB and pass the album.
	 *    This avoids a second DB query later.
	 *  - If you do not have a {@link AbstractAlbum} instance, and you won't
	 *    need one later, simply pass the ID of the album.
	 *    This avoids the overhead of model hydration.
	 *
	 * @param string|int|AbstractAlbum|null $albumModelOrID
	 *
	 * @return bool
	 */
	public function isAccessible($albumModelOrID): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}

		// deal with root album first
		if (empty($albumModelOrID)) {
			return AccessControl::is_logged_in();
		}

		/** @var ?AbstractAlbum $album */
		/** @var int|string $albumID */
		list($albumID, $album) = $this->disassembleAlbumParameter($albumModelOrID);

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum($albumID);
		}

		// Deal with albums that are actual models.
		// If we already have an instance of a model, then avoid an unnecessary
		// DB query.
		// We perform the accessibility checks directly on the album model.
		// The semantics of these checks must be kept in sync with the
		// checks in `applyAccessibilityFilter`.
		if ($album) {
			/* @var BaseAlbum $album */

			if (!AccessControl::is_logged_in()) {
				return
					($album->is_public && $album->password === null) ||
					($album->is_public && $this->isAlbumUnlocked($album->id));
			} else {
				$userID = AccessControl::id();

				return
					($album->owner_id === $userID) ||
					($album->shared_with()->where('user_id', '=', $userID)->count()) ||
					($album->is_public && $album->password === null) ||
					($album->is_public && $this->isAlbumUnlocked($album->id));
			}
		} else {
			// If we don't have an instance of a model, then use
			// `applyAccessibilityFilter` to build a query, but don't hydrate
			// a model
			return $this->applyAccessibilityFilter(
				BaseAlbumImpl::query()->where('id', '=', intval($albumID))
			)->count() !== 0;
		}
	}

	/**
	 * Pushes an album ID onto the stack of unlocked albums.
	 *
	 * @param int $albumID
	 */
	public function unlockAlbum(int $albumID): void
	{
		Session::push(self::UNLOCKED_ALBUMS_SESSION_KEY, $albumID);
	}

	/**
	 * Check if the given album ID has previously been unlocked.
	 *
	 * @param int $albumID
	 *
	 * @return bool
	 */
	public function isAlbumUnlocked(int $albumID): bool
	{
		return in_array($albumID, $this->getUnlockedAlbumIDs());
	}

	private function getUnlockedAlbumIDs(): array
	{
		return Session::get(self::UNLOCKED_ALBUMS_SESSION_KEY, []);
	}

	/**
	 * Checks whether the albums with the given IDs are editable by the
	 * current user.
	 *
	 * An album is called _editable_ if the current user is allowed to edit
	 * the album's properties.
	 * This also covers adding new photos to an album.
	 * An album is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user has the upload privilege and is the owner of the album
	 *
	 * Note about built-in smart albums:
	 * The built-in smart albums (starred, public, recent, unsorted) do not
	 * have any editable properties.
	 * Hence, it is pointless whether a smart album is editable or not.
	 * In order to silently ignore/skip this condition for smart albums,
	 * this method always returns `true` for a smart album.
	 *
	 * @param array<mixed, string|int> $albumIDs
	 *
	 * @return bool
	 */
	public function areEditable(array $albumIDs): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}
		if (!AccessControl::is_logged_in()) {
			return false;
		}

		$user = AccessControl::user();

		if (!$user->upload) {
			return false;
		}

		// Remove smart albums (they get a pass).
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$albumIDs = array_diff(array_unique($albumIDs), array_keys(AlbumFactory::BUILTIN_SMARTS));
		if (count($albumIDs) > 0) {
			return BaseAlbumImpl::query()
				->whereIn('id', $albumIDs)
				->where('owner_id', '=', $user->id)
				->count() === count($albumIDs);
		}

		return true;
	}

	/**
	 * Throws an exception if the given query does not query for an album.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @param Builder $query
	 */
	private function failForWrongQueryModel(Builder $query): void
	{
		$model = $query->getModel();
		if (!($model instanceof Album || $model instanceof TagAlbum || $model instanceof BaseAlbumImpl)) {
			throw new \InvalidArgumentException('the given query does not query for albums');
		}
	}

	/**
	 * This method sorts the passed multi-typed parameter into the correct
	 * return type.
	 *
	 * This method returns a pair [albumID, album] acc. to the following rules
	 *  - if `$in === null` is passed in, the result is `[0, null]`
	 *  - if an ID is passed in, i.e. if `$in` is an integer or string, the
	 *    result is `[$in, null]`, i.e. the input parameter is returned as
	 *    the ID of an album
	 *  - if an album is passed in, i.e. if `$in` is an instance of
	 *    {@link AbstractAlbum}, then the result is `[$in->id, $in]`, i.e. the
	 *    input parameter is returned as the album and the ID is extracted.
	 *
	 * Note, this method never loads any model from database.
	 *
	 * @param string|int|AbstractAlbum|null $in
	 *
	 * @return array an array with [albumID, album]
	 */
	private function disassembleAlbumParameter($in): array
	{
		if ($in instanceof AbstractAlbum) {
			return [$in->id, $in];
		} else {
			return [$in ?: 0, null];
		}
	}

	/**
	 * This is the common code to decide whether the given smart album is
	 * visible/accessible by the current user.
	 *
	 * Note, that the logic for visibility and/or accessibility of a smart
	 * album is identical.
	 *
	 * @param string $smartAlbumID
	 *
	 * @return bool true, if the smart album is visible/accessible by the user
	 */
	private function isAuthorizedForSmartAlbum(string $smartAlbumID): bool
	{
		return
			(AccessControl::is_logged_in() && AccessControl::can_upload()) ||
			$this->albumFactory->createSmartAlbum($smartAlbumID)->is_public;
	}
}
