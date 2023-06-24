<?php

namespace App\Policies;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Builders\AlbumBuilder;
use App\Models\Builders\TagAlbumBuilder;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class AlbumQueryPolicy.
 */
class AlbumQueryPolicy
{
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
	 * Note this makes use of the fact that when an album is NOT shared nor public, the value of is_link_required is NULL.
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
				// We laverage that IS_LINK_REQUIRED is NULL if the album is NOT shared publically (left join).
				->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
				->when(
					$userID !== null,
					// Current user is the owner of the album
					fn ($q) => $q
						->orWhere('base_albums.owner_id', '=', $userID)
				);
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
	 *  - **`computed_access_permissions`**.
	 *
	 * Moreover, the raw OR-clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * Note this makes use of the fact that when an album is NOT shared nor public, the value of is_link_required is NULL.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return BaseBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function appendAccessibilityConditions(BaseBuilder $query): BaseBuilder
	{
		$unlockedAlbumIDs = AlbumPolicy::getUnlockedAlbumIDs();
		$userID = Auth::id();

		try {
			$query
				->orWhere(
					// Album is public/shared (visible or not) and NOT protected by a password
					fn (BaseBuilder $q) => $q
						->whereNotNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED)
						->whereNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
				)
				->orWhere(
					// Album is public/shared (visible or not) and protected by a password and unlocked
					fn (BaseBuilder $q) => $q
						->whereNotNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED)
						->whereNotNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
						->whereIn(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID, $unlockedAlbumIDs)
				)
				->when(
					$userID !== null,
					// Current user is the owner of the album
					fn (BaseBuilder $q) => $q
						->orWhere('base_albums.owner_id', '=', $userID)
				);

			return $query;
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Restricts an album query to _reachable_ albums.
	 *
	 * An album is called _reachable_, if it is _visible_ and _accessible_ simultaneously.
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

		$unlockedAlbumIDs = AlbumPolicy::getUnlockedAlbumIDs();
		$userID = Auth::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		// The sub-query only uses properties (i.e. columns) which are
		// defined on the common base model for all albums.
		$reachabilitySubQuery = function (Builder $query2) use ($unlockedAlbumIDs, $userID) {
			$query2
				->where(
					// Album is visible and not password protected.
					fn (Builder $q) => $q
						->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
						->whereNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
				)
				->orWhere(
					// Album is visible and password protected and unlocked
					fn (Builder $q) => $q
						->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', false)
						->where(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
						->whereIn(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID, $unlockedAlbumIDs)
				)
				->when(
					$userID !== null,
					// User is owner of the album
					fn (Builder $q) => $q
						->orWhere('base_albums.owner_id', '=', $userID)
				);
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
				$q,
				null,
				null,
			);
		});
	}

	/**
	 * Adds the conditions of an unreachable album to the query.
	 *
	 * An album is called _unreachable_, if it is
	 *   - _invisible_
	 *   - or not _accessible_
	 *
	 * It is the opposite of "reachable", if the user is not able to see the album
	 * within its parent album or does not have the privilege to enter it.
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

		$unlockedAlbumIDs = AlbumPolicy::getUnlockedAlbumIDs();
		$userID = Auth::id();

		try {
			// There are inner albums ...
			$builder
				->from('albums', 'inner')
				->join('base_albums as inner_base_albums', 'inner_base_albums.id', '=', 'inner.id');

			$this->joinSubComputedAccessPermissions($builder, 'inner.id', 'left', 'inner_');

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

			/**
			 *                        | Link required <> false | Password required = true
			 * -----------------------+------------------------+--------------------------
			 * Link required <> false | Not reachable ✓        | Not reachable ✓
			 * Id not Unlocked        | Not reachable ✓        | Not reachable ✓.
			 */
			$builder
				->where(
					fn (BaseBuilder $q) => $q
						->where('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', true)
						->orWhereNull('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED)
						->orWhereNotNull('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
						// ->orWhere('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_PASSWORD_REQUIRED, '=', true)
				)
				->where(
					fn (BaseBuilder $q) => $q
						->where('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED, '=', true)
						->orWhereNull('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::IS_LINK_REQUIRED)
						->orWhereNotIn('inner_' . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID, $unlockedAlbumIDs)
				)
				->when(
					$userID !== null,
					fn (BaseBuilder $q) => $q
						->where('inner_base_albums.owner_id', '<>', $userID)
				);

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

		// TODO: would joining just owner_id from base_album make more sense ?
		if ($model instanceof Album || $model instanceof TagAlbum) {
			$query->join('base_albums', 'base_albums.id', '=', $table . '.id');
		}

		$this->joinSubComputedAccessPermissions($query, $table . '.id', 'left');
	}

	/**
	 * Generate the computed property for the possibly logged-in user.
	 *
	 * This produces a sub table with base_album_id where we compute:
	 * - base_album_id so that we can link those computed property to the base_album table.
	 * - is_link_required => MIN as we want to ensure that a logged in user can see the shared album
	 * - grants_full_photo_access => MAX as the public setting takes priority
	 * - grants_download => MAX as the public setting takes priority
	 * - grants_upload => MAX as the shared setting takes priority
	 * - grants_edit => MAX as the shared setting takes priority
	 * - grants_delete => MAX as the shared setting takes priority
	 *
	 * @return BaseBuilder
	 */
	public function getComputedAccessPermissionSubQuery(): BaseBuilder
	{
		// $driver = DB::getDriverName();
		// $passwordLengthIsBetween0and1 = match ($driver) {
		// 	'pgsql' => $this->getPasswordIsRequiredPgSQL(),
		// 	'sqlite' => $this->getPasswordIsRequiredSqlite(),
		// 	default => $this->getPasswordIsRequiredMySQL()
		// };

		$select = [
			APC::BASE_ALBUM_ID,
			APC::IS_LINK_REQUIRED,
			// APC::GRANTS_FULL_PHOTO_ACCESS,
			// APC::GRANTS_DOWNLOAD,
			// APC::GRANTS_UPLOAD,
			// APC::GRANTS_EDIT,
			// APC::GRANTS_DELETE,
			APC::PASSWORD
			// APC::IS_PASSWORD_REQUIRED
			// DB::raw($passwordLengthIsBetween0and1 . ' as ' . APC::IS_PASSWORD_REQUIRED),
		];

		return DB::table('access_permissions', APC::COMPUTED_ACCESS_PERMISSIONS)->select($select)
			->when(Auth::check(),
				fn ($q1) => 
					$q1->where(APC::USER_ID, '=', Auth::id())
						->orWhere(fn($q2) => 
							$q2->whereNull(APC::USER_ID)
								->whereNotIn(DB::table('access_permissions')->select('id')->where(APC::USER_ID, '=', Auth::id()))
						))
			->when(!Auth::check(),
				fn ($q1) => $q1->whereNull(APC::USER_ID));
	}

	// private function getPasswordIsRequiredMySQL(): string
	// {
	// 	return '1 - ISNULL(' . APC::PASSWORD . ')';
	// }

	// private function getPasswordIsRequiredSqlite(): string
	// {
	// 	// sqlite does not support ISNULL(x) -> bool
	// 	// We convert password to empty string if it is null
	// 	$passwordIsDefined = 'IFNULL(' . APC::PASSWORD . ',"")';
	// 	// Take the lengh
	// 	$passwordLength = 'LENGTH(' . $passwordIsDefined . ')';
	// 	// First min with 1 to upper bound it
	// 	// then MIN aggregation
	// 	return 'MIN(' . $passwordLength . ',1)';
	// }

	// private function getPasswordIsRequiredPgSQL(): string
	// {
	// 	// pgsql has a proper boolean support and does not support ISNULL(x) -> bool
	// 	// If password is null, length returns null, we replace the value by 0 in such case
	// 	$passwordLength = 'COALESCE(LENGTH(' . APC::PASSWORD . '),0)';
	// 	// We take the minimum between length and 1 with LEAST
	// 	// and then agggregate on the column with MIN
	// 	// before casting it to bool
	// 	return 'LEAST(' . $passwordLength . ',1)::bool';
	// }

	/**
	 * Helper to join the the computed property for the possibly logged-in user.
	 *
	 * This produces a sub table with base_album_id where we compute:
	 * - base_album_id so that we can link those computed property to the album table.
	 * - is_link_required => MIN as we want to ensure that a logged in user can see the shared album
	 * - grants_full_photo_access => MAX as the public setting takes priority
	 * - grants_download => MAX as the public setting takes priority
	 * - grants_upload => MAX as the shared setting takes priority
	 * - grants_edit => MAX as the shared setting takes priority
	 * - grants_delete => MAX as the shared setting takes priority
	 * - is_password_required => If password is null, ISNULL returns 1. We use MAX as the shared setting takes priority. We then want the negation on that.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder|BaseBuilder $query
	 * @param string                                     $second
	 * @param string                                     $prefix
	 * @param string                                     $type
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function joinSubComputedAccessPermissions(
		AlbumBuilder|FixedQueryBuilder|BaseBuilder $query,
		string $second = 'base_albums.id',
		string $type = 'left',
		string $prefix = '',
	): void {
		$query->joinSub(
			query: $this->getComputedAccessPermissionSubQuery(),
			as: $prefix . APC::COMPUTED_ACCESS_PERMISSIONS,
			first: $prefix . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID,
			operator: '=',
			second: $second,
			type: $type
		);
	}
}
