<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Feature 076 (FR-076-04, ADR-076-01): joins a regular album's automatic
 * cover row (`album_user_thumbs`, `is_precomputed`) onto a listing query and
 * selects its ranks as `auto_cover_id`, `auto_cover_id_2`, `auto_cover_id_3`.
 *
 * The viewer decides the join shape here, in PHP, so the SQL never branches
 * on viewer identity:
 * - admin: the owner's row;
 * - guest: the `NULL` row;
 * - logged-in user: their own row (their owned albums, or an album shared
 *   with them alone), else the `NULL` row. The `NULL` row is only joined
 *   when no own row matched, so the three ranks always come from one row.
 *
 * Every join hits the `(album_id, user_id_unique_key)` unique index. The
 * query must already be joined with `albums` and `base_albums`.
 */
final class JoinAutoCover
{
	private const TABLE = 'album_user_thumbs';
	private const OWN = 'auto_cover_own';
	private const PUBLIC = 'auto_cover_public';

	/**
	 * @template TQuery of \Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Database\Query\Builder
	 *
	 * @param TQuery $query
	 *
	 * @return TQuery
	 */
	public static function apply($query, ?User $user)
	{
		return match (true) {
			$user?->may_administrate === true => self::single($query, 'base_albums.owner_id'),
			$user === null => self::single($query, null),
			default => self::ownElsePublic($query, $user->id),
		};
	}

	/**
	 * One join on `user_id_unique_key` = the owner column, or = 0 (the `NULL` row).
	 *
	 * @template TQuery of \Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Database\Query\Builder
	 *
	 * @param TQuery $query
	 *
	 * @return TQuery
	 */
	private static function single($query, ?string $owner_column)
	{
		$query->leftJoin(self::TABLE . ' as ' . self::OWN, function (JoinClause $join) use ($owner_column): void {
			$join->on(self::OWN . '.album_id', '=', 'albums.id')->where(self::OWN . '.is_precomputed', '=', true);
			$owner_column === null
				? $join->where(self::OWN . '.user_id_unique_key', '=', 0)
				: $join->on(self::OWN . '.user_id_unique_key', '=', $owner_column);
		});

		return $query->addSelect([
			self::OWN . '.photo_id as auto_cover_id',
			self::OWN . '.photo_id_2 as auto_cover_id_2',
			self::OWN . '.photo_id_3 as auto_cover_id_3',
		]);
	}

	/**
	 * The viewer's own row, and the `NULL` row only where the former is missing.
	 *
	 * @template TQuery of \Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Database\Query\Builder
	 *
	 * @param TQuery $query
	 *
	 * @return TQuery
	 */
	private static function ownElsePublic($query, int $user_id)
	{
		$query->leftJoin(self::TABLE . ' as ' . self::OWN, fn (JoinClause $join) => $join
			->on(self::OWN . '.album_id', '=', 'albums.id')
			->where(self::OWN . '.is_precomputed', '=', true)
			->where(self::OWN . '.user_id_unique_key', '=', $user_id));
		$query->leftJoin(self::TABLE . ' as ' . self::PUBLIC, fn (JoinClause $join) => $join
			->on(self::PUBLIC . '.album_id', '=', 'albums.id')
			->where(self::PUBLIC . '.is_precomputed', '=', true)
			->where(self::PUBLIC . '.user_id_unique_key', '=', 0)
			->whereNull(self::OWN . '.id'));

		return $query->addSelect([
			DB::raw('COALESCE(' . self::OWN . '.photo_id, ' . self::PUBLIC . '.photo_id) as auto_cover_id'),
			DB::raw('COALESCE(' . self::OWN . '.photo_id_2, ' . self::PUBLIC . '.photo_id_2) as auto_cover_id_2'),
			DB::raw('COALESCE(' . self::OWN . '.photo_id_3, ' . self::PUBLIC . '.photo_id_3) as auto_cover_id_3'),
		]);
	}
}
