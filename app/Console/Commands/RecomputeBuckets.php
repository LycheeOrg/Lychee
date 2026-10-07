<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Assets\DbBool;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Services\AlbumBucketComputer;
use App\Services\PhotoBucketComputer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bulk-recomputes `bucket_id` for every album, then for every `photo_album`
 * row — joins what used to be two separate commands
 * (`lychee:recompute-album-buckets`, `lychee:recompute-photo-buckets`) into
 * one, since deployers always need both after an instance-wide
 * sorting/timeline/title-bucket default change or an initial-deploy backfill.
 *
 * Album pass derives every value from the album row itself plus its
 * resolved parent's sort-column/granularity settings, via one chunked
 * self-join query — never touches `photos`/`photo_album`. Photo pass derives
 * every value from the `photo_album` row's own `photo_id`/`album_id` pair's
 * already-loaded photo columns plus the containing album's resolved
 * sorting/timeline settings, via a keyset-paginated scan of `photo_album`
 * plus one primary-key lookup per page into `photos`/`base_albums` — never
 * touches `size_variants`/`tags`.
 */
class RecomputeBuckets extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'lychee:recompute-buckets
							{--chunk=1000 : Number of rows to process per batch}';

	/**
	 * @var string
	 */
	protected $description = 'Bulk-recompute the bucket_id column for every album and every photo_album row.';

	public function handle(AlbumBucketComputer $album_bucket_computer, PhotoBucketComputer $photo_bucket_computer): int
	{
		$chunk_size = max(1, (int) $this->option('chunk'));

		$this->recomputeAlbumBuckets($album_bucket_computer, $chunk_size);
		$this->newLine();
		$this->recomputePhotoBuckets($photo_bucket_computer, $chunk_size);

		return Command::SUCCESS;
	}

	private function recomputeAlbumBuckets(AlbumBucketComputer $bucket_computer, int $chunk_size): void
	{
		$total = DB::table('albums')->count();
		$this->info("Found {$total} albums to process");

		if ($total === 0) {
			$this->info('No albums to process');

			return;
		}

		$global_default_column = AlbumSortingCriterion::createDefault()->column;

		$bar = $this->output->createProgressBar($total);
		$bar->start();
		$processed = 0;

		DB::table('albums as a')
			->join('base_albums as ab', 'ab.id', '=', 'a.id')
			->leftJoin('albums as p', 'p.id', '=', 'a.parent_id')
			->select([
				'a.id',
				'ab.title',
				'ab.title_base',
				'ab.created_at',
				'a.min_taken_at',
				'a.max_taken_at',
				'p.album_sorting_col',
				'p.album_sorting_order',
				'p.album_timeline',
			])
			->orderBy('a.id')
			->chunkById(
				$chunk_size,
				function (Collection $rows) use (&$processed, $bar, $bucket_computer, $global_default_column): void {
					$updates = [];
					foreach ($rows as $row) {
						$sorting_column = $row->album_sorting_col !== null
							? ColumnSortingType::from($row->album_sorting_col)
							: $global_default_column;

						$candidate_granularity = $row->album_timeline !== null
							? TimelineAlbumGranularity::from($row->album_timeline)
							: null;
						$granularity = $bucket_computer->resolveGranularity($candidate_granularity);

						$updates[] = [
							'id' => $row->id,
							'bucket_id' => $bucket_computer->compute(
								sorting_column: $sorting_column,
								granularity: $granularity,
								title: $row->title,
								title_base: $row->title_base,
								created_at: Carbon::parse($row->created_at),
								min_taken_at: $row->min_taken_at !== null ? Carbon::parse($row->min_taken_at) : null,
								max_taken_at: $row->max_taken_at !== null ? Carbon::parse($row->max_taken_at) : null,
							),
						];
					}
					$processed += $rows->count();
					$bar->advance($rows->count());

					DB::table('albums')->upsert($updates, ['id'], ['bucket_id']);
				},
				column: 'a.id',
				alias: 'id',
			);

		$bar->finish();
		$this->newLine(2);

		$this->info("Recomputed bucket_id for {$processed} albums");
		Log::info("Bucket backfill completed: {$processed} albums processed");
	}

	private function recomputePhotoBuckets(PhotoBucketComputer $bucket_computer, int $chunk_size): void
	{
		$total = DB::table('photo_album')->count();
		$this->info("Found {$total} photo_album rows to process");

		if ($total === 0) {
			$this->info('No photo_album rows to process');

			return;
		}

		$global_default_column = PhotoSortingCriterion::createDefault()->column;

		$bar = $this->output->createProgressBar($total);
		$bar->start();
		$processed = 0;

		$cursor = null;
		do {
			$keys = $this->photoAlbumKeyPage($cursor, $chunk_size);
			$updates = $this->computePhotoAlbumBuckets($keys, $bucket_computer, $global_default_column);

			if (count($updates) > 0) {
				DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_id']);
			}

			$processed += $keys->count();
			$bar->advance($keys->count());
			$last = $keys->last();
			$cursor = $last !== null ? [$last->photo_id, $last->album_id] : null;
		} while ($keys->count() === $chunk_size);

		$bar->finish();
		$this->newLine(2);

		$this->info("Recomputed bucket_id for {$processed} photo_album rows");
		Log::info("Photo bucket backfill completed: {$processed} photo_album rows processed");
	}

	/**
	 * One page of `photo_album` primary keys, ordered by `(photo_id, album_id)`
	 * and resumed strictly after `$cursor` (the last key pair of the previous
	 * page, `null` for the first page).
	 *
	 * Keyset pagination on the pivot table alone: an `OFFSET` page must
	 * produce and discard every preceding row, and a page joined with
	 * `photos`/`base_albums` lets the planner drive the join from the small
	 * `base_albums` table and sort every remaining row per page — both make a
	 * full pass quadratic in the table size. A single-table query ordered by
	 * its own primary key leaves every supported driver one plan: a primary
	 * key range seek on the leading `photo_id >= ?` bound. The `OR` only
	 * filters the remaining rows of the cursor's own photo, so a photo linked
	 * into several albums is never split or skipped across a page boundary.
	 *
	 * @param array{0:string,1:string}|null $cursor
	 *
	 * @return Collection<int,object{photo_id:string,album_id:string}>
	 */
	private function photoAlbumKeyPage(?array $cursor, int $chunk_size): Collection
	{
		$query = DB::table('photo_album')
			->select(['photo_id', 'album_id'])
			->orderBy('photo_id')
			->orderBy('album_id')
			->limit($chunk_size);

		if ($cursor !== null) {
			[$photo_id, $album_id] = $cursor;
			$query->where('photo_id', '>=', $photo_id)
				->where(fn ($q) => $q->where('photo_id', '>', $photo_id)->orWhere('album_id', '>', $album_id));
		}

		return $query->get();
	}

	/**
	 * Computes the `bucket_id` of every `photo_album` row in `$keys`, loading
	 * the page's photo columns and containing albums' sorting/timeline
	 * settings with one primary-key lookup each.
	 *
	 * @param Collection<int,object{photo_id:string,album_id:string}> $keys
	 *
	 * @return array<int,array{photo_id:string,album_id:string,bucket_id:string|null}>
	 */
	private function computePhotoAlbumBuckets(Collection $keys, PhotoBucketComputer $bucket_computer, ColumnSortingType $global_default_column): array
	{
		$photos = DB::table('photos')
			->select(['id', 'title', 'title_base', 'created_at', 'taken_at', 'is_highlighted', 'type', 'rating_avg'])
			->whereIn('id', $keys->pluck('photo_id')->unique()->all())
			->get()
			->keyBy('id');

		$albums = DB::table('base_albums')
			->select(['id', 'sorting_col', 'photo_timeline'])
			->whereIn('id', $keys->pluck('album_id')->unique()->all())
			->get()
			->keyBy('id');

		$updates = [];
		foreach ($keys as $key) {
			$photo = $photos->get($key->photo_id);
			$album = $albums->get($key->album_id);
			if ($photo === null || $album === null) {
				continue;
			}

			$sorting_column = $album->sorting_col !== null
				? ColumnSortingType::from($album->sorting_col)
				: $global_default_column;

			$candidate_granularity = $album->photo_timeline !== null
				? TimelinePhotoGranularity::from($album->photo_timeline)
				: null;
			$granularity = $bucket_computer->resolveGranularity($candidate_granularity);

			$updates[] = [
				'photo_id' => $key->photo_id,
				'album_id' => $key->album_id,
				'bucket_id' => $bucket_computer->compute(
					sorting_column: $sorting_column,
					granularity: $granularity,
					title: $photo->title,
					title_base: $photo->title_base ?? '',
					created_at: Carbon::parse($photo->created_at),
					taken_at: $photo->taken_at !== null ? Carbon::parse($photo->taken_at) : null,
					is_highlighted: DbBool::parse($photo->is_highlighted),
					type: $photo->type ?? '',
					rating_avg: $photo->rating_avg,
				),
			];
		}

		return $updates;
	}
}
