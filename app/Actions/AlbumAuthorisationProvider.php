<?php

namespace App\Actions;

use App\Contracts\AbstractAlbum;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
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

		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibilitySubQuery = function (Builder $query2) use ($userID) {
			$query2
				->where(fn (Builder $q) => $q
					->where('requires_link', '=', false)
					->where('is_public', '=', true)
			);
			if ($userID !== null) {
				$query2
					->orWhere('owner_id', '=', $userID)
					->orWhere(fn (Builder $q) => $q
						->where('requires_link', '=', false)
						->whereHas(
							'shared_with',
							fn (Builder $q2) => $q2->where('user_id', '=', $userID)
						)
					);
			}
		};

		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
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

		if (AccessControl::is_admin()) {
			return $query;
		}

		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an inner query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$accessibilitySubQuery = function (Builder $query2) use ($unlockedAlbumIDs, $userID) {
			$query2
				->where(fn (Builder $q) => $q
					->where('is_public', '=', true)
					->whereNull('password')
				)
				->orWhere(fn (Builder $q) => $q
					->where('is_public', '=', true)
					->whereIn('id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$query2
					->orWhere('owner_id', '=', $userID)
					->orWhereHas(
						'shared_with',
						fn (Builder $q) => $q->where('user_id', '=', $userID)
					);
			}
		};

		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		if ($query->getModel() instanceof BaseAlbumImpl) {
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
	 * @param string|int|null $albumID
	 *
	 * @return bool
	 */
	public function isAccessible($albumID): bool
	{
		// the admin may access everything, the root album may be access by everybody
		if (AccessControl::is_admin() || empty($albumID)) {
			return true;
		}

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum($albumID);
		}

		// Use `applyAccessibilityFilter` to build a query, but don't hydrate
		// a model
		return $this->applyAccessibilityFilter(
			BaseAlbumImpl::query()->where('id', '=', intval($albumID))
		)->count() !== 0;
	}

	/**
	 * Restricts an album query to _browsable_ albums.
	 *
	 * Intuitively, an album is browsable if users can find a path to the
	 * album by "clicking around".
	 * The definition of "browsability" is recursive by nature.
	 * In order for an album to be browsable, all parent albums up to the
	 * origin must be browsable, too.
	 * Please note, that the origin is not necessarily identical to the root
	 * album.
	 *
	 * A "mathematical" definition follows:
	 *
	 * An album is called _not blocked_, if the user can reach the album
	 * from its direct parent.
	 * In order to be not blocked, the album must be _visible_ and
	 * _accessible_ at the same time, i.e. the user must be able to see the
	 * album and must have the privilege to enter it.
	 * See {@link AlbumAuthorisationProvider::applyVisibilityFilter()} and
	 * {@link AlbumAuthorisationProvider::applyAccessibilityFilter()} for
	 * the respective definitions.
	 * The combination of both sets of conditions yields that an album is
	 * _not blocked_, if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is the admin, or
	 *  - the user is the owner, or
	 *  - the album does not require a direct link and is shared with the user, or
	 *  - the album does not require a direct link, is public and has no password set, or
	 *  - the album does not require a direct link, is public and has been unlocked
	 *
	 * An album is called _browsable_, if
	 *
	 *   1. there is a path from the origin to the album, and
	 *   2. no _blocked_ albums on the path from the origin to the album exist.
	 *
	 * Note that the worst case efficiency of this query is O(n²), if n is
	 * the number of query results.
	 * The query does "not know" that albums are organized in a tree structure
	 * and thus re-examines the entire path for each album in the result and
	 * does not take a short-cut for sub-paths which has already been examined
	 * earlier.
	 * In other words for a flat tree (all result nodes are direct children
	 * of the origin), the runtime is O(n), but for a high tree (the nodes are
	 * basically a sequence), the runtime is O(n²).
	 */
	public function applyBrowsabilityFilter(Builder $query, ?Album $origin = null): Builder
	{
		if (!($query->getModel() instanceof Album)) {
			throw new \InvalidArgumentException('the given query does not query for albums');
		}

		if (AccessControl::is_admin()) {
			return $query;
		}

		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// Sub-query for blocked albums
		$blockedAlbums = function (BaseBuilder $builder) use ($origin, $unlockedAlbumIDs, $userID) {
			// There are inner albums ...
			$builder->from('albums', 'inner');
			// ... on the path from the origin ...
			if ($origin) {
				$builder
					// (We must exclude the origin as an inner node
					// because the origin might have set "require_link", but
					// we do not care, because the user has already got
					// somehow into the origin)
					->where('inner._lft', '>', $origin->_lft)
					->where('inner._rgt', '<', $origin->_rgt);
			}
			// ... to the child ...
			$builder
				// (We must include the final child into the list of
				// inner nodes, because we must also check if the child final
				// child is blocked.)
				->whereColumn('inner._lft', '<=', 'child._lft')
				->whereColumn('inner._rgt', '>=', 'child._rgt');
			// ... which are blocked.
			$builder
				->where(fn (BaseBuilder $q) => $q
					->where('inner.requires_link', '=', true)
					->orWhere('inner.is_public', '=', false)
					->orWhereNotNull('inner.password')
				)
				->where(fn (BaseBuilder $q) => $q
					->where('inner.requires_link', '=', true)
					->orWhere('inner.is_public', '=', false)
					->orWhereNotIn('inner.id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$builder
					->where('inner.owner_id', '<>', $userID)
					->where(fn (BaseBuilder $q) => $q
						->where('inner.requires_link', '=', true)
						->orWhereNotExists(fn (BaseBuilder $q2) => $q2
							->from('user_base_album')
							->where('user_id', '=', $userID)
						)
					);
			}
		};

		// Create a new album query ...
		$finalQuery = Album::query()
			// ... which eagerly loads the same relations as the original query ...
			->setEagerLoads($query->getEagerLoads())
			// ..., wraps the original query ...
			->fromSub($query->toBase(), 'child');
		// ... and ensures that only those albums of the original query are
		// returned for which a path from the origin to the album exist ...
		if ($origin) {
			$finalQuery
				// (We include the origin here, because we want the
				// origin to be browsable from itself)
				->where('child._lft', '>=', $origin->_lft)
				->where('child._rgt', '<=', $origin->_rgt);
		}
		// ... such that there are no blocked albums on the path to the album.
		$finalQuery->whereNotExists($blockedAlbums);

		return $finalQuery;
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
