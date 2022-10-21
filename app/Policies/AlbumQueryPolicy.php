<?php

namespace App\Policies;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\AlbumBuilder;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\TagAlbumBuilder;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

/**
 * Class AlbumQueryPolicy.
 */
class AlbumQueryPolicy extends BasePolicy
{
	protected AlbumFactory $albumFactory;
	protected AlbumPolicy $albumPolicy;

	public function __construct(AlbumFactory $albumFactory, AlbumPolicy $albumPolicy)
	{
		$this->albumFactory = $albumFactory;
		$this->albumPolicy = $albumPolicy;
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
	 *  - the album is shared with the user
	 *  - the album is public and the album does not require a direct link
	 *
	 * @param AlbumBuilder|TagAlbumBuilder $query
	 *
	 * @return AlbumBuilder|TagAlbumBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function applyVisibilityFilter(AlbumBuilder|FixedQueryBuilder $query): AlbumBuilder|TagAlbumBuilder
	{
		$this->prepareModelQueryOrFail($query);

		if (Auth::user()?->may_administrate === true) {
			return $query;
		}

		$userID = Auth::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$visibilitySubQuery = function (AlbumBuilder|TagAlbumBuilder $query2) use ($userID) {
			$query2
				->where(
					fn (AlbumBuilder|TagAlbumBuilder $q) => $q
						->where('base_albums.is_link_required', '=', false)
						->where('base_albums.is_public', '=', true)
				);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere('user_base_album.user_id', '=', $userID);
			}
		};

		return $query->where($visibilitySubQuery);
	}

	/**
	 * Adds the conditions of an accessible album to the query.
	 *
	 * **Attention:** This method is only meant for internal use by
	 * this class or {@link PhotoQueryPolicy}.
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
	 * @param BaseBuilder $query
	 *
	 * @return BaseBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function appendAccessibilityConditions(BaseBuilder $query): BaseBuilder
	{
		$unlockedAlbumIDs = $this->albumPolicy->getUnlockedAlbumIDs();
		$userID = Auth::id();

		try {
			$query
				->orWhere(
					fn (BaseBuilder $q) => $q
						->where('base_albums.is_public', '=', true)
						->whereNull('base_albums.password')
				)
				->orWhere(
					fn (BaseBuilder $q) => $q
						->where('base_albums.is_public', '=', true)
						->whereIn('base_albums.id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$query
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere('user_base_album.user_id', '=', $userID);
			}

			return $query;
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Restricts an album query to _reachable_ albums.
	 *
	 * An album is called _reachable_, if it is _visible_ and _accessible_
	 * simultaneously.
	 * An album is reachable, if the user is able to see the album
	 * within its parent album and has the privilege to enter it.
	 *
	 *
	 * The combination of both sets of conditions yields that an album is
	 * _reachable_, if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is the admin, or
	 *  - the user is the owner, or
	 *  - the album is shared with the user, or
	 *  - the album does not require a direct link, is public and has no password set, or
	 *  - the album does not require a direct link, is public and has been unlocked
	 *
	 * @param AlbumBuilder $query
	 *
	 * @return AlbumBuilder
	 *
	 * @throws QueryBuilderException
	 * @throws InvalidQueryModelException
	 */
	public function applyReachabilityFilter(AlbumBuilder $query): AlbumBuilder
	{
		$this->prepareModelQueryOrFail($query);

		if (Auth::user()?->may_administrate === true) {
			return $query;
		}

		$unlockedAlbumIDs = $this->albumPolicy->getUnlockedAlbumIDs();
		$userID = Auth::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$reachabilitySubQuery = function (Builder $query2) use ($userID, $unlockedAlbumIDs) {
			$query2
				->where(
					fn (Builder $q) => $q
						->where('base_albums.is_link_required', '=', false)
						->where('base_albums.is_public', '=', true)
						->whereNull('base_albums.password')
				)
				->orWhere(
					fn (Builder $q) => $q
						->where('base_albums.is_link_required', '=', false)
						->where('base_albums.is_public', '=', true)
						->whereIn('base_albums.id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$query2
					->orWhere('base_albums.owner_id', '=', $userID)
					->orWhere('user_base_album.user_id', '=', $userID);
			}
		};

		return $query->where($reachabilitySubQuery);
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
	 * See {@link AlbumQueryPolicy::applyReachabilityFilter()}
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
	 * @param AlbumBuilder $query the album query which shall be restricted
	 *
	 * @return AlbumBuilder the restricted album query
	 *
	 * @throws InternalLycheeException
	 */
	public function applyBrowsabilityFilter(AlbumBuilder $query): AlbumBuilder
	{
		$table = $query->getQuery()->from;
		if (!($query->getModel() instanceof Album) || $table !== 'albums') {
			throw new LycheeInvalidArgumentException('the given query does not query for albums');
		}

		if (Auth::user()?->may_administrate === true) {
			return $query;
		}

		// Ensures that only those albums of the original query are
		// returned for which a path from the origin to the album exist
		// such that there are no blocked albums on the path to the album.
		return $query->whereNotExists(function (BaseBuilder $q) {
			$this->appendUnreachableAlbumsCondition(
				$q, null, null,
			);
		});
	}

	/**
	 * Adds the conditions of an unreachable album to the query.
	 *
	 * **Attention:** This method is only meant for internal use by
	 * this class or {@link PhotoQueryPolicy}.
	 * Use {@link AlbumQueryPolicy::applyBrowsabilityFilter()}
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
	 * @throws InternalLycheeException
	 */
	public function appendUnreachableAlbumsCondition(BaseBuilder $builder, int|string|null $originLeft, int|string|null $originRight): BaseBuilder
	{
		if (gettype($originLeft) !== gettype($originRight)) {
			throw new LycheeInvalidArgumentException('$originLeft and $originRight must simultaneously either be integers, strings or null');
		}

		$unlockedAlbumIDs = $this->albumPolicy->getUnlockedAlbumIDs();
		$userID = Auth::id();

		try {
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
			} elseif (is_string($originLeft) && is_string($originRight)) {
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
				->where(
					fn (BaseBuilder $q) => $q
						->where('inner_base_albums.is_link_required', '=', true)
						->orWhere('inner_base_albums.is_public', '=', false)
						->orWhereNotNull('inner_base_albums.password')
				)
				->where(
					fn (BaseBuilder $q) => $q
						->where('inner_base_albums.is_link_required', '=', true)
						->orWhere('inner_base_albums.is_public', '=', false)
						->orWhereNotIn('inner_base_albums.id', $unlockedAlbumIDs)
				);
			if ($userID !== null) {
				$builder
					->where('inner_base_albums.owner_id', '<>', $userID)
					->whereNotExists(
						fn (BaseBuilder $q) => $q
							->from('user_base_album', 'user_inner_base_album')
							->whereColumn('user_inner_base_album.base_album_id', '=', 'inner_base_albums.id')
							->where('user_inner_base_album.user_id', '=', $userID)
					);
			}

			return $builder;
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Throws an exception if the given query does not query for an album.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder $query
	 *
	 * @throws QueryBuilderException
	 * @throws InvalidQueryModelException
	 */
	private function prepareModelQueryOrFail(AlbumBuilder|FixedQueryBuilder $query): void
	{
		$model = $query->getModel();
		$table = $query->getQuery()->from;
		if (
			!($model instanceof Album ||
				$model instanceof TagAlbum ||
				$model instanceof BaseAlbumImpl
			) ||
			$table !== $model->getTable()
		) {
			throw new InvalidQueryModelException('album');
		}

		// Ensure that only columns of the targeted model are selected,
		// if no specific columns are yet set.
		// Otherwise, we cannot add a JOIN clause below
		// without accidentally adding all columns of the join, too.
		$baseQuery = $query->getQuery();
		if ($baseQuery->columns === null || count($baseQuery->columns) === 0) {
			$query->select([$table . '.*']);
		}

		if ($model instanceof Album || $model instanceof TagAlbum) {
			$query->join('base_albums', 'base_albums.id', '=', $table . '.id');
		}

		$userID = Auth::id();
		if ($userID !== null) {
			// We must left join with `user_base_album` if and only if we
			// restrict the eventual query to the ID of the authenticated
			// user by a `WHERE`-clause.
			// If we were doing a left join unconditionally, then some
			// albums might appear multiple times as part of the result
			// because an album might be shared with more than one user.
			// Hence, we must restrict the `LEFT JOIN` to the user ID which
			// is also used in the outer `WHERE`-clause.
			// See `applyVisibilityFilter` and `appendAccessibilityConditions`.
			$query->leftJoin(
				'user_base_album',
				function (JoinClause $join) use ($userID) {
					$join
						->on('user_base_album.base_album_id', '=', 'base_albums.id')
						->where('user_base_album.user_id', '=', $userID);
				}
			);
		}
	}
}
