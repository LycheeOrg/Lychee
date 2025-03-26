<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;

class PhotoQueryPolicy
{
	protected AlbumQueryPolicy $album_query_policy;

	public function __construct()
	{
		$this->album_query_policy = resolve(AlbumQueryPolicy::class);
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
	 *    (cp. {@link AlbumQueryPolicy::isAccessible()}).
	 *  - the photo is public
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 *
	 * @return FixedQueryBuilder<Photo>
	 *
	 * @throws InternalLycheeException
	 */
	public function applyVisibilityFilter(FixedQueryBuilder $query): FixedQueryBuilder
	{
		$this->prepareModelQueryOrFail($query, false, true);

		if (Auth::user()?->may_administrate === true) {
			return $query;
		}

		$user_id = Auth::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		$visibility_sub_query = function (FixedQueryBuilder $query2) use ($user_id): void {
			$this->album_query_policy->appendAccessibilityConditions($query2->getQuery());
			if ($user_id !== null) {
				$query2->orWhere('photos.owner_id', '=', $user_id);
			}
		};

		return $query->where($visibility_sub_query);
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
	 * See {@link AlbumQueryPolicy::applyBrowsabilityFilter()}
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
	 * @param FixedQueryBuilder<Photo> $query        the photo query which shall be restricted
	 * @param Album|null               $origin       the optional top album which is used as a search base
	 * @param bool                     $include_nsfw include also the photos in sensitive albums
	 *
	 * @return FixedQueryBuilder<Photo> the restricted photo query
	 *
	 * @throws InternalLycheeException
	 */
	public function applySearchabilityFilter(FixedQueryBuilder $query, ?Album $origin = null, bool $include_nsfw = true): FixedQueryBuilder
	{
		$this->prepareModelQueryOrFail($query, true, false);

		// If origin is set, also restrict the search result for admin
		// to photos which are in albums below origin.
		// This is not a security filter, but simply functional.
		if ($origin !== null) {
			$query
				->where('albums._lft', '>=', $origin->_lft)
				->where('albums._rgt', '<=', $origin->_rgt);
		}

		if (!$include_nsfw) {
			$query->where(fn (Builder $query) => $this->appendSensitivityConditions($query->getQuery(), $origin?->_lft, $origin?->_rgt));
		}

		if (Auth::user()?->may_administrate === true) {
			return $query;
		}

		return $query->where(function (Builder $query) use ($origin): void {
			$this->appendSearchabilityConditions(
				$query->getQuery(),
				$origin?->_lft,
				$origin?->_rgt
			);
		});
	}

	/**
	 * Adds the conditions of _searchable_ photos to the query.
	 *
	 * **Attention:** This method is only meant for internal use.
	 * Use {@link PhotoQueryPolicy::applySearchabilityFilter()}
	 * if called from other places instead.
	 *
	 * This method adds the WHERE conditions without any further pre-cautions.
	 * The method silently assumes that the SELECT clause contains the tables
	 *
	 *  - **`albums`**.
	 *
	 * See {@link AlbumQueryPolicy::applySearchabilityFilter()}
	 * for a definition of a searchable photo.
	 *
	 * Moreover, the raw clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param BaseBuilder     $query        the photo query which shall be restricted
	 * @param int|string|null $origin_left  optionally constraints the search base;
	 *                                      an integer value is interpreted a raw left bound of the search base;
	 *                                      a string value is interpreted as a reference to a column which shall be used as a left bound
	 * @param int|string|null $origin_right like `$origin_left` but for the right bound
	 *
	 * @return BaseBuilder the restricted photo query
	 *
	 * @throws QueryBuilderException
	 */
	public function appendSearchabilityConditions(BaseBuilder $query, int|string|null $origin_left, int|string|null $origin_right): BaseBuilder
	{
		$user_id = Auth::id();

		try {
			// there must be no unreachable album between the origin and the photo
			$query->whereNotExists(function (BaseBuilder $q) use ($origin_left, $origin_right): void {
				$this->album_query_policy->appendUnreachableAlbumsCondition($q, $origin_left, $origin_right);
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

			if ($user_id !== null) {
				$query->orWhere('photos.owner_id', '=', $user_id);
			}
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}

		return $query;
	}

	/**
	 * Adds the conditions of _sensitive_ photos to the query.
	 *
	 * **Attention:** This method is only meant for internal use.
	 * Use {@link PhotoQueryPolicy::applySearchabilityFilter()}
	 * if called from other places instead.
	 *
	 * This method adds the WHERE conditions without any further pre-cautions.
	 * The method silently assumes that the SELECT clause contains the tables
	 *
	 *  - **`albums`**.
	 *
	 * Moreover, the raw clauses are added.
	 * They are not wrapped into a nesting braces `()`.
	 *
	 * @param BaseBuilder $query the photo query which shall be restricted
	 *
	 * @return BaseBuilder the restricted photo query
	 *
	 * @throws QueryBuilderException
	 */
	private function appendSensitivityConditions(BaseBuilder $query, int|string|null $origin_left, int|string|null $origin_right): BaseBuilder
	{
		$user_id = Auth::id();

		try {
			// there must be no unreachable album between the origin and the photo
			$query->whereNotExists(function (BaseBuilder $q) use ($origin_left, $origin_right): void {
				$this->album_query_policy->appendRecursiveSensitiveAlbumsCondition($q, $origin_left, $origin_right);
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
			$query->orWhere(
				fn ($q) => $q
					->whereNull('photos.album_id')
					->where('photos.owner_id', '=', $user_id)
			);
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}

		return $query;
	}

	/**
	 * Throws an exception if the given query does not query for a photo.
	 *
	 * @param FixedQueryBuilder<Photo> $query           the query to prepare
	 * @param bool                     $add_albums      if true, joins photo query with (parent) albums
	 * @param bool                     $add_base_albums if true, joins photos query with (parent) base albums
	 *
	 * @throws InternalLycheeException
	 */
	private function prepareModelQueryOrFail(FixedQueryBuilder $query, bool $add_albums, bool $add_base_albums): void
	{
		$model = $query->getModel();
		$table = $query->getQuery()->from;
		if (!($model instanceof Photo && $table === 'photos')) {
			throw new InvalidQueryModelException('photo');
		}

		// Ensure that only columns of the photos are selected,
		// if no specific columns are yet set.
		// Otherwise, we cannot add a JOIN clause below
		// without accidentally adding all columns of the join, too.
		$base_query = $query->getQuery();
		if ($base_query->columns === null || count($base_query->columns) === 0) {
			$query->select(['photos.*']);
		}
		if ($add_albums) {
			$query->leftJoin(
				table: 'albums',
				first: 'albums.id',
				operator: '=',
				second: 'photos.album_id');
		}
		if ($add_base_albums) {
			$query->leftJoin(
				table: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'photos.album_id');
		}

		// Necessary to apply the visibiliy/search conditions
		$this->album_query_policy->joinSubComputedAccessPermissions(
			query: $query,
			second: 'photos.album_id'
		);
	}
}