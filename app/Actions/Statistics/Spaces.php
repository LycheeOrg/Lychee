<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Statistics;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SizeVariantType;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * We compute the size usage from the size variants.
 * Do note that this number may be slightly off due to the way we store pictures in the database:
 * row are duplicates for pictures, but the file is stored only once.
 */
final class Spaces
{
	/**
	 * Return the amount of data stored on the server (optionally for a user).
	 *
	 * Uses pre-computed album_size_statistics table for <100ms performance.
	 * Joins user's owned albums and sums their statistics.
	 *
	 * @param int|null $owner_id
	 *
	 * @return Collection<int,array{id:int,username:string,size:int}>
	 */
	public function getFullSpacePerUser(?int $owner_id = null): Collection
	{
		return DB::table('users')
			->when($owner_id !== null, fn ($query) => $query->where('users.id', '=', $owner_id))
			->leftJoinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id']),
				as: 'base_albums',
				first: 'base_albums.owner_id',
				operator: '=',
				second: 'users.id'
			)
			->leftJoin('album_size_statistics', 'album_size_statistics.album_id', '=', 'base_albums.id')
			->select(
				'users.id',
				'username',
				DB::raw('SUM(COALESCE(album_size_statistics.size_thumb, 0) +
					COALESCE(album_size_statistics.size_thumb2x, 0) +
					COALESCE(album_size_statistics.size_small, 0) +
					COALESCE(album_size_statistics.size_small2x, 0) +
					COALESCE(album_size_statistics.size_medium, 0) +
					COALESCE(album_size_statistics.size_medium2x, 0) +
					COALESCE(album_size_statistics.size_original, 0) +
					COALESCE(album_size_statistics.size_raw, 0)) as size')
			)
			->groupBy('users.id', 'username')
			->orderBy('users.id', 'asc')
			->get()
			->map(fn ($item) => [
				'id' => intval($item->id),
				'username' => strval($item->username),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return the amount of data stored on the server (optionally for a user).
	 *
	 * Uses pre-computed album_size_statistics for photos in albums,
	 * plus direct size_variants query for photos not in any album.
	 *
	 * @param int|null $owner_id
	 *
	 * @return Collection<int,array{type:SizeVariantType,size:int}>
	 */
	public function getSpacePerSizeVariantTypePerUser(?int $owner_id = null): Collection
	{
		// Query 1: Get sizes from album_size_statistics for photos in albums
		$album_stats = DB::table('base_albums')
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->join('album_size_statistics', 'album_size_statistics.album_id', '=', 'base_albums.id')
			->select(
				DB::raw('SUM(COALESCE(album_size_statistics.size_raw, 0)) as size_raw'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_original, 0)) as size_original'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_medium2x, 0)) as size_medium2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_medium, 0)) as size_medium'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_small2x, 0)) as size_small2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_small, 0)) as size_small'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_thumb2x, 0)) as size_thumb2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_thumb, 0)) as size_thumb')
			)
			->first();

		// Query 2: Get sizes from size_variants for photos NOT in any album
		// SELECT
		//     `size_variants_select`.`type`,
		//     SUM(size_variants_select.filesize) as size
		// FROM
		//     (
		//         SELECT
		//             `size_variants`.`type`,
		//             `size_variants`.`filesize`
		//         FROM
		//             `size_variants`
		//             left join `photo_album` on `photo_album`.`photo_id` = `size_variants`.`photo_id`
		//         WHERE
		//             `photo_album`.`album_id` IS NULL
		//     ) as `size_variants_select`
		// WHERE
		//     `size_variants_select`.`type` != 8  -- PLACEHOLDER
		// GROUP BY
		//     `size_variants_select`.`type`;
		// With 3285540 size_variants, this takes ~33s on a DB on a SSD
		//

		// This simpler version takes 8~9s instead.
		// SELECT
		//     `size_variants`.`type`,
		//     `size_variants`.`filesize`
		// FROM
		//     `size_variants`
		//     left join `photo_album` on `photo_album`.`photo_id` = `size_variants`.`photo_id`
		// WHERE
		//     `photo_album`.`album_id` IS NULL;
		//
		// We do the grouping in PHP, this will be faster.
		$size_variants = DB::table('size_variants')
			->select('size_variants.type', 'size_variants.filesize')
			->leftJoin(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'size_variants.photo_id')
			->whereNull(PA::ALBUM_ID)
			->when($owner_id !== null,
				fn ($query) => $query->joinSub(
					query: DB::table('photos')
						->select(['photos.id', 'photos.owner_id'])
						->where('photos.owner_id', '=', $owner_id),
					as: 'photos',
					first: 'photos.id',
					operator: '=',
					second: 'size_variants.photo_id'
				)
			)
			->get();

		$accumulator = [];
		foreach ($size_variants as $item) {
			if ($item->type === SizeVariantType::PLACEHOLDER->value) {
				// skip the placeholders variants
				continue;
			}
			$accumulator[$item->type] = ($accumulator[$item->type] ?? 0) + intval($item->filesize);
		}

		// Combine results by SizeVariantType
		$combined = [
			SizeVariantType::RAW->value => intval($album_stats->size_raw ?? 0),
			SizeVariantType::ORIGINAL->value => intval($album_stats->size_original ?? 0),
			SizeVariantType::MEDIUM2X->value => intval($album_stats->size_medium2x ?? 0),
			SizeVariantType::MEDIUM->value => intval($album_stats->size_medium ?? 0),
			SizeVariantType::SMALL2X->value => intval($album_stats->size_small2x ?? 0),
			SizeVariantType::SMALL->value => intval($album_stats->size_small ?? 0),
			SizeVariantType::THUMB2X->value => intval($album_stats->size_thumb2x ?? 0),
			SizeVariantType::THUMB->value => intval($album_stats->size_thumb ?? 0),
		];

		// Add unalbummed photo sizes
		foreach ($accumulator as $type => $item) {
			$combined[$type] += intval($item);
		}

		// Convert to collection and filter out zero sizes
		return collect($combined)
			->filter(fn ($size) => $size > 0)
			->map(fn ($size, $type) => [
				'type' => SizeVariantType::from($type),
				'size' => $size,
			])
			->values();
	}

	/**
	 * Return the amount of data stored on the server (optionally for an album).
	 *
	 * Uses pre-computed album_size_statistics table for performance.
	 *
	 * @param string $album_id
	 *
	 * @return Collection<int,array{type:SizeVariantType,size:int}>
	 */
	public function getSpacePerSizeVariantTypePerAlbum(string $album_id): Collection
	{
		$query = DB::table('albums')
			->where('albums.id', '=', $album_id)
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt'),
				as: 'descendants',
				first: function (JoinClause $join): void {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->leftJoin('album_size_statistics', 'album_size_statistics.album_id', '=', 'descendants.id')
			->select(
				DB::raw('SUM(COALESCE(album_size_statistics.size_raw, 0)) as size_raw'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_original, 0)) as size_original'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_medium2x, 0)) as size_medium2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_medium, 0)) as size_medium'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_small2x, 0)) as size_small2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_small, 0)) as size_small'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_thumb2x, 0)) as size_thumb2x'),
				DB::raw('SUM(COALESCE(album_size_statistics.size_thumb, 0)) as size_thumb')
			);

		$result = $query->first();

		// Map aggregated sizes to SizeVariantType enum
		$variants = [];
		if (intval($result->size_raw) > 0) {
			$variants[] = ['type' => SizeVariantType::RAW, 'size' => intval($result->size_raw)];
		}
		if (intval($result->size_original) > 0) {
			$variants[] = ['type' => SizeVariantType::ORIGINAL, 'size' => intval($result->size_original)];
		}
		if (intval($result->size_medium2x) > 0) {
			$variants[] = ['type' => SizeVariantType::MEDIUM2X, 'size' => intval($result->size_medium2x)];
		}
		if (intval($result->size_medium) > 0) {
			$variants[] = ['type' => SizeVariantType::MEDIUM, 'size' => intval($result->size_medium)];
		}
		if (intval($result->size_small2x) > 0) {
			$variants[] = ['type' => SizeVariantType::SMALL2X, 'size' => intval($result->size_small2x)];
		}
		if (intval($result->size_small) > 0) {
			$variants[] = ['type' => SizeVariantType::SMALL, 'size' => intval($result->size_small)];
		}
		if (intval($result->size_thumb2x) > 0) {
			$variants[] = ['type' => SizeVariantType::THUMB2X, 'size' => intval($result->size_thumb2x)];
		}
		if (intval($result->size_thumb) > 0) {
			$variants[] = ['type' => SizeVariantType::THUMB, 'size' => intval($result->size_thumb)];
		}

		return collect($variants);
	}

	/**
	 * Return size statistics per album.
	 *
	 * Uses pre-computed album_size_statistics table for performance.
	 * Albums without statistics will report size as 0.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,left:int,right:int,size:int}>
	 */
	public function getSpacePerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null,
				fn ($query) => $query
					->joinSub(
						query: DB::table('albums', 'parent')->select('parent.id', 'parent._lft', 'parent._rgt'),
						as: 'parent',
						first: function (JoinClause $join): void {
							$join->on('albums._lft', '>=', 'parent._lft')
								->on('albums._rgt', '<=', 'parent._rgt');
						}
					)
					->where('parent.id', '=', $album_id)
			)
			->when($owner_id !== null, fn ($query) => $query->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
			->where('base_albums.owner_id', '=', $owner_id))
			->leftJoin('album_size_statistics', 'album_size_statistics.album_id', '=', 'albums.id')
			->select(
				'albums.id',
				'albums._lft',
				'albums._rgt',
				DB::raw('(COALESCE(album_size_statistics.size_raw, 0) +
					COALESCE(album_size_statistics.size_thumb, 0) +
					COALESCE(album_size_statistics.size_thumb2x, 0) +
					COALESCE(album_size_statistics.size_small, 0) +
					COALESCE(album_size_statistics.size_small2x, 0) +
					COALESCE(album_size_statistics.size_medium, 0) +
					COALESCE(album_size_statistics.size_medium2x, 0) +
					COALESCE(album_size_statistics.size_original, 0)) as size')
			)
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Same as above but with full size (including sub-albums).
	 *
	 * Uses pre-computed album_size_statistics table with nested set query
	 * to find descendants and sum their statistics.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,left:int,right:int,size:int}>
	 */
	public function getTotalSpacePerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->when($owner_id !== null, fn ($query) => $query->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
				->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt'),
				as: 'descendants',
				first: function (JoinClause $join): void {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->leftJoin('album_size_statistics', 'album_size_statistics.album_id', '=', 'descendants.id')
			->select(
				'albums.id',
				'albums._lft',
				'albums._rgt',
				DB::raw('SUM(COALESCE(album_size_statistics.size_raw, 0) +
					COALESCE(album_size_statistics.size_thumb, 0) +
					COALESCE(album_size_statistics.size_thumb2x, 0) +
					COALESCE(album_size_statistics.size_small, 0) +
					COALESCE(album_size_statistics.size_small2x, 0) +
					COALESCE(album_size_statistics.size_medium, 0) +
					COALESCE(album_size_statistics.size_medium2x, 0) +
					COALESCE(album_size_statistics.size_original, 0)) as size')
			)->groupBy('albums.id', 'albums._lft', 'albums._rgt')
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return size statistics (number of photos rather than bytes) per album.
	 *
	 * Uses the pre-computed num_photos column from the albums table for performance.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}>
	 */
	public function getPhotoCountPerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null,
				fn ($query) => $query
					->joinSub(
						query: DB::table('albums', 'parent')->select('parent.id', 'parent._lft', 'parent._rgt'),
						as: 'parent',
						first: function (JoinClause $join): void {
							$join->on('albums._lft', '>=', 'parent._lft')
								->on('albums._rgt', '<=', 'parent._rgt');
						}
					)
					->where('parent.id', '=', $album_id)
			)
			->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id', 'base_albums.title', 'base_albums.is_nsfw']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('users')->select(['users.id', 'users.username']),
				as: 'users',
				first: 'users.id',
				operator: '=',
				second: 'base_albums.owner_id'
			)
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
				'albums.num_photos'
			)
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'username' => strval($item->username),
				'title' => strval($item->title),
				'is_nsfw' => boolval($item->is_nsfw),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
			]);
	}

	/**
	 * Same as above but including sub-albums.
	 *
	 * Uses the pre-computed num_photos column from the albums table,
	 * summed across all descendants for performance.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}>
	 */
	public function getTotalPhotoCountPerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id', 'base_albums.title', 'base_albums.is_nsfw']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt', 'descendants.num_photos'),
				as: 'descendants',
				first: function (JoinClause $join): void {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->joinSub(
				query: DB::table('users')->select(['users.id', 'users.username']),
				as: 'users',
				first: 'users.id',
				operator: '=',
				second: 'base_albums.owner_id'
			)
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
				DB::raw('SUM(descendants.num_photos) as num_photos')
			)->groupBy(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
			)
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'username' => strval($item->username),
				'title' => strval($item->title),
				'is_nsfw' => boolval($item->is_nsfw),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
			]);
	}
}
