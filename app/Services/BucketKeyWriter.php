<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Chunked bulk writes of `photo_album.bucket_key` and `albums.bucket_key`
 * (FR-092-14): at most `$chunk_size` rows per statement, so no statement
 * exceeds a driver's bind-parameter limit. Shared by the recompute jobs and
 * `lychee:recompute-buckets`.
 */
final class BucketKeyWriter
{
	public const CHUNK_SIZE = 1000;

	public function __construct(
		private readonly PhotoBucketComputer $photo_bucket_computer,
		private readonly AlbumBucketComputer $album_bucket_computer,
	) {
	}

	/**
	 * Rewrites the key of every `photo_album` row `$scope` selects.
	 *
	 * @param \Closure(Builder):mixed                                                     $scope    restricts `photo_album`
	 * @param \Closure(Collection<int,object{photo_id:string,album_id:string}>):void|null $on_chunk called after each written chunk
	 *
	 * @return int rows processed
	 */
	public function writePhotoAlbumKeys(\Closure $scope, int $chunk_size = self::CHUNK_SIZE, ?\Closure $on_chunk = null): int
	{
		$chunk_size = max(1, $chunk_size);
		$default_column = PhotoSortingCriterion::createDefault()->column;
		$processed = 0;
		$cursor = null;
		do {
			$keys = $this->photoAlbumKeyPage($scope, $cursor, $chunk_size);
			$updates = $this->photoAlbumKeys($keys, $default_column);
			if ($updates !== []) {
				DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_key']);
			}

			$processed += $keys->count();
			if ($on_chunk !== null && $keys->isNotEmpty()) {
				$on_chunk($keys);
			}
			$last = $keys->last();
			$cursor = $last !== null ? [$last->photo_id, $last->album_id] : null;
		} while ($keys->count() === $chunk_size);

		return $processed;
	}

	/**
	 * Rewrites the key of every album `$scope` selects (on `albums as a`).
	 *
	 * @param \Closure(Builder):mixed                    $scope    restricts `albums as a`
	 * @param \Closure(Collection<int,object>):void|null $on_chunk called after each written chunk
	 *
	 * @return int rows processed
	 */
	public function writeAlbumKeys(\Closure $scope, int $chunk_size = self::CHUNK_SIZE, ?\Closure $on_chunk = null): int
	{
		$default_column = AlbumSortingCriterion::createDefault()->column;
		$processed = 0;

		$query = DB::table('albums as a')
			->join('base_albums as ab', 'ab.id', '=', 'a.id')
			->leftJoin('albums as p', 'p.id', '=', 'a.parent_id')
			->select(['a.id', 'ab.title', 'ab.title_base', 'ab.created_at', 'a.min_taken_at', 'a.max_taken_at', 'p.album_sorting_col', 'p.album_sorting_order']);
		$scope($query);

		$query->orderBy('a.id')->chunkById(
			max(1, $chunk_size),
			function (Collection $rows) use ($default_column, &$processed, $on_chunk): void {
				$updates = $rows->map(fn (object $row): array => [
					'id' => $row->id,
					'bucket_key' => $this->album_bucket_computer->keyForRow(
						self::effectiveColumn($row->album_sorting_col, $row->album_sorting_order, $default_column),
						$row,
					),
				])->all();
				DB::table('albums')->upsert($updates, ['id'], ['bucket_key']);

				$processed += $rows->count();
				if ($on_chunk !== null) {
					$on_chunk($rows);
				}
			},
			column: 'a.id',
			alias: 'id',
		);

		return $processed;
	}

	/**
	 * Effective sort column: the album's own when both column and order are
	 * set (mirrors `getEffectivePhotoSorting()`/`getEffectiveAlbumSorting()`),
	 * else the instance default.
	 */
	public static function effectiveColumn(?string $column, ?string $order, ColumnSortingType $default): ColumnSortingType
	{
		return ($column === null || $order === null) ? $default : ColumnSortingType::from($column);
	}

	/**
	 * Keyset page of `photo_album` primary keys after `$cursor`, on the pivot
	 * table alone: a single-table query ordered by its own primary key keeps
	 * every driver on a key range seek. The `OR` only filters the remaining
	 * rows of the cursor's own photo, so no row is split or skipped across a
	 * page boundary.
	 *
	 * @param \Closure(Builder):mixed       $scope
	 * @param array{0:string,1:string}|null $cursor
	 *
	 * @return Collection<int,object{photo_id:string,album_id:string}>
	 */
	private function photoAlbumKeyPage(\Closure $scope, ?array $cursor, int $chunk_size): Collection
	{
		$query = DB::table('photo_album')
			->select(['photo_id', 'album_id'])
			->orderBy('photo_id')
			->orderBy('album_id')
			->limit($chunk_size);
		$scope($query);

		if ($cursor !== null) {
			[$photo_id, $album_id] = $cursor;
			$query->where('photo_id', '>=', $photo_id)
				->where(fn (Builder $q) => $q->where('photo_id', '>', $photo_id)->orWhere('album_id', '>', $album_id));
		}

		/** @var Collection<int,object{photo_id:string,album_id:string}> */
		return $query->get();
	}

	/**
	 * Keys of one page, loading its photos and albums with one primary-key
	 * lookup each.
	 *
	 * @param Collection<int,object{photo_id:string,album_id:string}> $keys
	 *
	 * @return array<int,array{photo_id:string,album_id:string,bucket_key:string|null}>
	 */
	private function photoAlbumKeys(Collection $keys, ColumnSortingType $default_column): array
	{
		if ($keys->isEmpty()) {
			return [];
		}

		$photos = DB::table('photos')
			->select(['id', ...PhotoBucketComputer::ROW_COLUMNS])
			->whereIn('id', $keys->pluck('photo_id')->unique()->all())
			->get()
			->keyBy('id');

		$columns = DB::table('base_albums')
			->select(['id', 'sorting_col', 'sorting_order'])
			->whereIn('id', $keys->pluck('album_id')->unique()->all())
			->get()
			->mapWithKeys(fn (object $album): array => [$album->id => self::effectiveColumn($album->sorting_col, $album->sorting_order, $default_column)]);

		$updates = [];
		foreach ($keys as $key) {
			$photo = $photos->get($key->photo_id);
			$column = $columns->get($key->album_id);
			if ($photo === null || $column === null) {
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$updates[] = [
				'photo_id' => $key->photo_id,
				'album_id' => $key->album_id,
				'bucket_key' => $this->photo_bucket_computer->keyForRow($column, $photo),
			];
		}

		return $updates;
	}
}
