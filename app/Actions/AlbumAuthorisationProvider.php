<?php

namespace App\Actions;

use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Session;

/**
 * Class AlbumAuthorisationProvider.
 */
class AlbumAuthorisationProvider
{
	public const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';
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
		$this->prepareModelQueryOrFail($query);

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
					->where('base_albums.requires_link', '=', false)
					->where('base_albums.is_public', '=', true)
			);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere(fn (Builder $q) => $q
						->where('base_albums.requires_link', '=', false)
						->where('user_base_album.user_id', '=', $userID)
					);
			}
		};

		return $query->where($visibilitySubQuery);
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
		$this->prepareModelQueryOrFail($query);

		if (AccessControl::is_admin()) {
			return $query;
		}

		return $query->where(
			fn (Builder $q) => $this->appendAccessibilityConditions($q->getQuery())
		);
	}

	/**
	 * Adds the conditions of an accessible album to the query.
	 *
	 * **Attention:** This method is only meant for internal use by
	 * this class or {@link PhotoAuthorisationProvider}.
	 * Use {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}
	 * if called from other places instead.
	 *
	 * This method adds the WHERE conditions without any further pre-cautions.
	 * The method silently assumes that the SELECT clause contains the tables
	 *
	 *  - **`base_albums`** and
	 *  - **`user_base_album`**.
	 *
	 * Moreover, the raw OR-clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 *
	 * @throws \InvalidArgumentException
	 */
	public function appendAccessibilityConditions(BaseBuilder $query): BaseBuilder
	{
		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		$query
			->orWhere(fn (BaseBuilder $q) => $q
				->where('base_albums.is_public', '=', true)
				->whereNull('base_albums.password')
			)
			->orWhere(fn (BaseBuilder $q) => $q
				->where('base_albums.is_public', '=', true)
				->whereIn('base_albums.id', $unlockedAlbumIDs)
			);
		if ($userID !== null) {
			$query
				->orWhere('base_albums.owner_id', '=', $userID)
				->orWhere('user_base_album.user_id', '=', $userID);
		}

		return $query;
	}

	/**
	 * Restricts an album query to _reachable_ albums.
	 *
	 * An album is called _reachable_, if it is _visible_ and _accessible_
	 * simultaneously.
	 * An album is reachable, if the user is able to see the album
	 * within its parent album and has the privilege to enter it.
	 *
	 * The result of this filter is strictly identical to the concatenation
	 * of {@link AlbumAuthorisationProvider::applyVisibilityFilter()} and
	 * {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}, i.e.
	 *
	 *     $aap = resolve(AlbumAuthorisationProvider::class);
	 *     $aap->applyVisibilityFilter(
	 *         $aap->applyAccessibilityFilter(
	 *             $model::query()
	 *         )
	 *     )->get()
	 *
	 * returns the exact same result set.
	 * The only advantage of this combined filter is that the `WHERE` clause
	 * is already in disjunctive normal form (DNF) which results in a
	 * slightly better SQL performance.
	 *
	 * The combination of both sets of conditions yields that an album is
	 * _reachable_, if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is the admin, or
	 *  - the user is the owner, or
	 *  - the album does not require a direct link and is shared with the user, or
	 *  - the album does not require a direct link, is public and has no password set, or
	 *  - the album does not require a direct link, is public and has been unlocked
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 *
	 * @throws \InvalidArgumentException
	 */
	public function applyReachabilityFilter(Builder $query): Builder
	{
		$this->prepareModelQueryOrFail($query);

		if (AccessControl::is_admin()) {
			return $query;
		}

		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$reachabilitySubQuery = function (Builder $query2) use ($userID, $unlockedAlbumIDs) {
			$query2
				->where(fn (Builder $q) => $q
					->where('base_albums.requires_link', '=', false)
					->where('base_albums.is_public', '=', true)
					->whereNull('base_albums.password')
				)
				->orWhere(fn (Builder $q) => $q
					->where('base_albums.requires_link', '=', false)
					->where('base_albums.is_public', '=', true)
					->whereIn('base_albums.id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere(fn (Builder $q) => $q
						->where('base_albums.requires_link', '=', false)
						->where('user_base_album.user_id', '=', $userID)
					);
			}
		};

		return $query->where($reachabilitySubQuery);
	}

	/**
	 * Checks whether the album is accessible by the current user.
	 *
	 * For real albums (i.e. albums that are stored in the DB), see
	 * {@link AlbumAuthorisationProvider::applyAccessibilityFilter()} for a
	 * specification of the rules when an album is accessible.
	 * In other cases, the following holds:
	 *  - the root album is accessible by everybody
	 *  - the built-in smart albums are accessible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is the album of recent photos and public by configuration, or
	 *     - the album is the album of starred photos and public by configuration
	 *
	 * @param string|null $albumID
	 *
	 * @return bool
	 */
	public function isAccessibleByID(?string $albumID): bool
	{
		// the admin may access everything, the root album may be accessed by everybody
		if (AccessControl::is_admin() || empty($albumID)) {
			return true;
		}

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum(
				$this->albumFactory->createSmartAlbum($albumID)
			);
		}

		// Use `applyAccessibilityFilter` to build a query, but don't hydrate
		// a model
		return $this->applyAccessibilityFilter(
			BaseAlbumImpl::query()->where('base_albums.id', '=', $albumID)
		)->count() !== 0;
	}

	public function isAccessible(Album $album): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}
		if (!AccessControl::is_logged_in()) {
			return
				($album->is_public && $album->password === null) ||
				($album->is_public && $this->isAlbumUnlocked($album->id));
		} else {
			$userID = AccessControl::id();

			return
				($album->owner_id === $userID) ||
				($album->is_public && $album->password === null) ||
				($album->is_public && $this->isAlbumUnlocked($album->id)) ||
				($album->shared_with()->where('user_id', '=', $userID)->count());
		}
	}

	/**
	 * Restricts an album query to _browsable_ albums.
	 *
	 * Intuitively, an album is browsable if users can find a path to the
	 * album by "clicking around".
	 * An album is called _browsable_, if
	 *
	 *   1. there is a path from the origin to the album, and
	 *   2. all albums on the path are _reachable_
	 *
	 * See {@link AlbumAuthorisationProvider::applyReachabilityFilter()}
	 * for the definition of reachability.
	 * Note, while _reachability_ (as well as _visibility_ and _accessibility_)
	 * are a _local_ properties, _browsability_ is a _global_ property.
	 *
	 * **Attention**:
	 * For efficiency reasons this method does not check if `$origin` itself
	 * is reachable.
	 * The method simply assumes that the user has already legitimately
	 * accessed the origin album, if the caller provides an album model.
	 *
	 * Due to constraints in the SQL syntax, the query actually checks that
	 *
	 *   1. there is a path from the origin to the album, and
	 *   2. no album on that path is unreachable
	 *
	 * Note that the worst case efficiency of this query is O(n²), if n is
	 * the number of query results.
	 * The query does not "know" that albums are organized in a tree structure
	 * and thus re-examines the entire path for each album in the result and
	 * does not take a short-cut for sub-paths which have already been examined
	 * previously.
	 * In other words for a flat tree (all result nodes are direct children
	 * of the origin), the runtime is O(n), but for a high tree (the nodes are
	 * basically a sequence), the runtime is O(n²).
	 *
	 * @param Builder    $query  the album query which shall be restricted
	 * @param Album|null $origin the optional top album which is used as a search base
	 *
	 * @return Builder the restricted album query
	 */
	public function applyBrowsabilityFilter(Builder $query, ?Album $origin = null): Builder
	{
		$table = $query->getQuery()->from;
		if (!($query->getModel() instanceof Album) || $table !== 'albums') {
			throw new \InvalidArgumentException('the given query does not query for albums');
		}

		// Ensures that only those albums of the original query are
		// returned for which a path from the origin to the album exist ...
		if ($origin) {
			$query
				// (We include the origin here, because we want the
				// origin to be browsable from itself)
				->where('albums._lft', '>=', $origin->_lft)
				->where('albums._rgt', '<=', $origin->_rgt);
		}

		// ... such that there are no blocked albums on the path to the album.
		if (AccessControl::is_admin()) {
			return $query;
		} else {
			return $query->whereNotExists(function (BaseBuilder $q) use ($origin) {
				$this->appendUnreachableAlbumsCondition(
					$q,
					$origin?->_lft,
					$origin?->_rgt,
				);
			});
		}
	}

	/**
	 * Adds the conditions of an unreachable album to the query.
	 *
	 * **Attention:** This method is only meant for internal use by
	 * this class or {@link PhotoAuthorisationProvider}.
	 * Use {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
	 * if called from other places instead.
	 *
	 * This method adds the WHERE conditions without any further pre-cautions.
	 * The method silently assumes that the passed query builder is used
	 * within an outer query whose SELECT clause contains the table
	 *
	 *  - **`albums`**.
	 *
	 * Moreover, the raw clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param BaseBuilder     $builder     the album query which shall be
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
	 * @return BaseBuilder
	 *
	 * @throws \InvalidArgumentException
	 */
	public function appendUnreachableAlbumsCondition(BaseBuilder $builder, int|string|null $originLeft, int|string|null $originRight): BaseBuilder
	{
		if (gettype($originLeft) !== gettype($originRight)) {
			throw new \InvalidArgumentException('$originLeft and $originRight must simultaneously either be integers, strings or null');
		}

		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;

		// There are inner albums ...
		$builder
			->from('albums', 'inner')
			->join('base_albums as inner_base_albums', 'inner_base_albums.id', '=', 'inner.id');
		// ... on the path from the origin ...
		if (is_int($originLeft)) {
			// (We must exclude the origin as an inner node
			// because the origin might have set "require_link", but
			// we do not care, because the user has already got
			// somehow into the origin)
			$builder
				->where('inner._lft', '>', $originLeft)
				->where('inner._rgt', '<', $originRight);
		} elseif (is_string($originLeft)) {
			$builder
				->whereColumn('inner._lft', '>', $originLeft)
				->whereColumn('inner._rgt', '<', $originRight);
		}
		// ... to the target ...
		$builder
			// (We must include the target into the list of inner nodes,
			// because we must also check whether the target is unreachable.)
			->whereColumn('inner._lft', '<=', 'albums._lft')
			->whereColumn('inner._rgt', '>=', 'albums._rgt');
		// ... which are unreachable.
		$builder
			->where(fn (BaseBuilder $q) => $q
				->where('inner_base_albums.requires_link', '=', true)
				->orWhere('inner_base_albums.is_public', '=', false)
				->orWhereNotNull('inner_base_albums.password')
			)
			->where(fn (BaseBuilder $q) => $q
				->where('inner_base_albums.requires_link', '=', true)
				->orWhere('inner_base_albums.is_public', '=', false)
				->orWhereNotIn('inner_base_albums.id', $unlockedAlbumIDs)
			);
		if ($userID !== null) {
			$builder
				->where('inner_base_albums.owner_id', '<>', $userID)
				->where(fn (BaseBuilder $q) => $q
					->where('inner_base_albums.requires_link', '=', true)
					->orWhereNotExists(fn (BaseBuilder $q2) => $q2
						->from('user_base_album', 'user_inner_base_album')
						->whereColumn('user_inner_base_album.base_album_id', '=', 'inner_base_albums.id')
						->where('user_inner_base_album.user_id', '=', $userID)
					)
				);
		}

		return $builder;
	}

	/**
	 * Pushes an album ID onto the stack of unlocked albums.
	 *
	 * @param string $albumID
	 */
	public function unlockAlbum(string $albumID): void
	{
		Session::push(self::UNLOCKED_ALBUMS_SESSION_KEY, $albumID);
	}

	/**
	 * Check if the given album ID has previously been unlocked.
	 *
	 * @param string $albumID
	 *
	 * @return bool
	 */
	public function isAlbumUnlocked(string $albumID): bool
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
	 * @param string[] $albumIDs
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

		if (!$user->may_upload) {
			return false;
		}

		// Remove root and smart albums (they get a pass).
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$albumIDs = array_diff(array_unique($albumIDs), array_keys(AlbumFactory::BUILTIN_SMARTS), [null]);
		if (count($albumIDs) > 0) {
			return BaseAlbumImpl::query()
				->whereIn('base_albums.id', $albumIDs)
				->where('base_albums.owner_id', '=', $user->id)
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
	private function prepareModelQueryOrFail(Builder $query): void
	{
		$model = $query->getModel();
		$table = $query->getQuery()->from;
		if (
			!(
				$model instanceof Album ||
				$model instanceof TagAlbum ||
				$model instanceof BaseAlbumImpl
			) ||
			$table !== $model->getTable()
		) {
			throw new \InvalidArgumentException('the given query does not query for albums');
		}

		// Ensure that only columns of the targeted model are selected,
		// if no specific columns are yet set.
		// Otherwise, we cannot add a JOIN clause below
		// without accidentally adding all columns of the join, too.
		if (empty($query->columns)) {
			$query->select([$table . '.*']);
		}

		if ($model instanceof Album || $model instanceof TagAlbum) {
			$query->join('base_albums', 'base_albums.id', '=', $table . '.id');
		}

		if (AccessControl::is_logged_in()) {
			$userID = AccessControl::id();
			// We must left join with `user_base_album` if and only if we
			// restrict the eventual query to the ID of the authenticated
			// user by a `WHERE`-clause.
			// If we were doing a left join unconditionally, then some
			// albums might appear multiple times as part of the result
			// because an album might be shared with more than one user.
			// Hence, we must restrict the `LEFT JOIN` to the user ID which
			// is also used in the outer `WHERE`-clause.
			// See `applyVisibilityFilter` and `appendAccessibilityConditions`.
			$query->leftJoin('user_base_album',
				function (JoinClause $join) use ($userID) {
					$join
						->on('user_base_album.base_album_id', '=', 'base_albums.id')
						->where('user_base_album.user_id', '=', $userID);
				}
			);
		}
	}

	/**
	 * This is the common code to decide whether the given smart album is
	 * visible/accessible by the current user.
	 *
	 * Note, that the logic for visibility and/or accessibility of a smart
	 * album is identical.
	 *
	 * @param BaseSmartAlbum $smartAlbum
	 *
	 * @return bool true, if the smart album is visible/accessible by the user
	 */
	public function isAuthorizedForSmartAlbum(BaseSmartAlbum $smartAlbum): bool
	{
		return
			(AccessControl::is_logged_in() && AccessControl::can_upload()) ||
			$smartAlbum->is_public;
	}
}
