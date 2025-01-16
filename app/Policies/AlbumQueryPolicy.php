<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	 * @param AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album> $query
	 *
	 * @return AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album>|TagAlbumBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function applyVisibilityFilter(AlbumBuilder|FixedQueryBuilder $query): AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder
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
				// Current user is the owner of the album
				// This is the case when is_link_required is NULL
				->when(
					$userID !== null,
					fn ($q) => $q->orWhere('base_albums.owner_id', '=', $userID)
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
					// Album is public/shared (visible or not => IS_LINK_REQUIRED NOT NULL)
					// and NOT protected by a password
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
					// TODO: move the owner to ACCESS PERMISSIONS so that we do not need to join base_album anymore
					// Current user is the owner of the album
					fn (BaseBuilder $q) => $q->orWhere('base_albums.owner_id', '=', $userID)
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
						->whereNotNull(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::PASSWORD)
						->whereIn(APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID, $unlockedAlbumIDs)
				)
				->when(
					$userID !== null,
					// User is owner of the album
					fn (Builder $q) => $q->orWhere('base_albums.owner_id', '=', $userID)
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
				->when(
					Auth::check(),
					fn ($q) => $this->joinBaseAlbumOwnerId($q, 'inner.id', 'inner_', false)
				);

			// WE MUST JOIN LEFT HERE
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
	 * Adds the conditions of a sensitive album by recursion to the query.
	 *
	 * An album is called _recursive sensitive_, if it is marked as sensitive or contains a sensitive parent.
	 *
	 * **Attention:** This method is only meant for internal use by
	 * this class or {@link PhotoQueryPolicy}.
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
	 * @param int|string|null $originLeft  optionally constrains the search
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
	public function appendRecursiveSensitiveAlbumsCondition(BaseBuilder $builder, int|string|null $originLeft, int|string|null $originRight): BaseBuilder
	{
		if (gettype($originLeft) !== gettype($originRight)) {
			throw new LycheeInvalidArgumentException('$originLeft and $originRight must simultaneously either be integers, strings or null');
		}

		try {
			// There are outers albums ...
			// WE MUST JOIN LEFT HERE
			$builder->from('albums', 'outers');
			$this->joinBaseAlbumSensitive($builder, 'outers.id', 'outers_');

			// ... on the path from the origin ...
			if (is_int($originLeft)) {
				// (We must exclude the origin as an outer node
				// because the origin might have set as is_nsfw, but
				// we do not care, because the user has already got
				// somehow into the origin)
				$builder
					->where('outers._lft', '>', $originLeft)
					->where('outers._rgt', '<', $originRight);
			} elseif (is_string($originLeft) && is_string($originRight)) {
				$builder
					->whereColumn('outers._lft', '>', $originLeft)
					->whereColumn('outers._rgt', '<', $originRight);
			}

			// ... to the target ...
			$builder
				// (We must include the target into the list of outer nodes,
				// because we must also check whether the target is unreachable.)
				->whereColumn('outers._lft', '<=', 'albums._lft')
				->whereColumn('outers._rgt', '>=', 'albums._rgt');
			// ... which are unreachable.

			$builder->where('outers_base_albums.is_nsfw', '=', true);

			return $builder;
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Throws an exception if the given query does not query for an album.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album> $query
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

		// We MUST do a full join because we are also sorting on created_at, title and description.
		// Those are stored in the base_albums.
		if ($model instanceof Album || $model instanceof TagAlbum) {
			$this->joinBaseAlbumOwnerId($query, $table . '.id');
		}

		// We MUST use left here because otherwise we are preventing any non shared album to be visible
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
	private function getComputedAccessPermissionSubQuery(bool $full = false): BaseBuilder
	{
		$select = [
			APC::BASE_ALBUM_ID,
			APC::IS_LINK_REQUIRED,
			APC::PASSWORD,
		];

		if ($full) {
			$select[] = APC::GRANTS_DELETE;
			$select[] = APC::GRANTS_EDIT;
			$select[] = APC::GRANTS_DOWNLOAD;
			$select[] = APC::GRANTS_FULL_PHOTO_ACCESS;
			$select[] = APC::GRANTS_UPLOAD;
			$select[] = APC::USER_ID;
		}
		$userId = Auth::id();

		return DB::table('access_permissions', APC::COMPUTED_ACCESS_PERMISSIONS)->select($select)
			->when(
				Auth::check(),
				fn ($q1) => $q1
					->where(APC::USER_ID, '=', $userId)
					->orWhere(
						fn ($q2) => $q2->whereNull(APC::USER_ID)
							->whereNotIn(
								APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID,
								fn ($q3) => $q3->select('acc_per.' . APC::BASE_ALBUM_ID)
									->from('access_permissions', 'acc_per')
									->where(APC::USER_ID, '=', $userId)
							)
					)
			)
			->when(
				!Auth::check(),
				fn ($q1) => $q1->whereNull(APC::USER_ID)
			);
	}

	/**
	 * Helper to join the the computed property for the possibly logged-in user.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album>|FixedQueryBuilder<\App\Models\Photo>|BaseBuilder $query  query to join to
	 * @param string                                                                                                             $second id to link with
	 * @param string                                                                                                             $prefix prefix in the future queries
	 * @param string                                                                                                             $type   left|inner
	 * @param bool                                                                                                               $full   Select most columns instead of just restricted
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
		bool $full = false,
	): void {
		$query->joinSub(
			query: $this->getComputedAccessPermissionSubQuery($full),
			as: $prefix . APC::COMPUTED_ACCESS_PERMISSIONS,
			first: $prefix . APC::COMPUTED_ACCESS_PERMISSIONS . '.' . APC::BASE_ALBUM_ID,
			operator: '=',
			second: $second,
			type: $type
		);
	}

	/**
	 * Join BaseAlbum for ownership and more.
	 * This aim to give lighter sub selection to make the queries run faster.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album>|BaseBuilder $query
	 * @param string                                                                        $second
	 * @param string                                                                        $prefix
	 * @param bool                                                                          $full
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function joinBaseAlbumOwnerId(
		AlbumBuilder|FixedQueryBuilder|BaseBuilder $query,
		string $second = 'inner.id',
		string $prefix = '',
		bool $full = true,
	): void {
		$columns = [
			$prefix . 'base_albums.id',
			$prefix . 'base_albums.owner_id',
		];

		if ($full) {
			$columns[] = $prefix . 'base_albums.title';
			$columns[] = $prefix . 'base_albums.created_at';
			$columns[] = $prefix . 'base_albums.description';
		}

		$query->joinSub(
			query: DB::table('base_albums', $prefix . 'base_albums')
				->select($columns),
			as: $prefix . 'base_albums',
			first: $prefix . 'base_albums.id',
			operator: '=',
			second: $second,
			type: 'left'
		);
	}

	/**
	 * Join BaseAlbum for sensitivity only.
	 * This aim to give lighter sub selection to make the queries run faster.
	 *
	 * @param AlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album>|BaseBuilder $query
	 * @param string                                                                        $second
	 * @param string                                                                        $prefix
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function joinBaseAlbumSensitive(
		AlbumBuilder|FixedQueryBuilder|BaseBuilder $query,
		string $second = 'inner.id',
		string $prefix = '',
	): void {
		$columns = [
			$prefix . 'base_albums.id',
			$prefix . 'base_albums.is_nsfw',
		];

		$query->joinSub(
			query: DB::table('base_albums', $prefix . 'base_albums')
				->select($columns),
			as: $prefix . 'base_albums',
			first: $prefix . 'base_albums.id',
			operator: '=',
			second: $second,
			type: 'left'
		);
	}
}
