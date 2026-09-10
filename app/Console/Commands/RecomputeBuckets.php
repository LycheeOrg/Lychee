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
 * sorting/timeline settings, via one chunked self-join query — never touches
 * `size_variants`/`tags`.
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
						$processed++;
						$bar->advance();
					}

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

		DB::table('photo_album as pa')
			->join('photos as p', 'p.id', '=', 'pa.photo_id')
			->join('base_albums as ba', 'ba.id', '=', 'pa.album_id')
			->select([
				'pa.photo_id',
				'pa.album_id',
				'p.title',
				'p.title_base',
				'p.created_at',
				'p.taken_at',
				'p.is_highlighted',
				'p.type',
				'p.rating_avg',
				'ba.sorting_col',
				'ba.photo_timeline',
			])
			->orderBy('pa.photo_id')
			->orderBy('pa.album_id')
			// `photo_album` has a composite primary key (photo_id, album_id),
			// no single-column unique id `chunkById()` could paginate on
			// without risking a row-group split across a chunk boundary for
			// a photo linked into several albums. Plain offset-based
			// chunk() is safe here instead: the loop below only ever writes
			// `bucket_id`, never one of the two ORDER BY columns, so the
			// result set's ordering (and therefore each page's offset window)
			// never shifts under us between chunks.
			->chunk(
				$chunk_size,
				function (Collection $rows) use (&$processed, $bar, $bucket_computer, $global_default_column): void {
					$updates = [];
					foreach ($rows as $row) {
						$sorting_column = $row->sorting_col !== null
							? ColumnSortingType::from($row->sorting_col)
							: $global_default_column;

						$candidate_granularity = $row->photo_timeline !== null
							? TimelinePhotoGranularity::from($row->photo_timeline)
							: null;
						$granularity = $bucket_computer->resolveGranularity($candidate_granularity);

						$updates[] = [
							'photo_id' => $row->photo_id,
							'album_id' => $row->album_id,
							'bucket_id' => $bucket_computer->compute(
								sorting_column: $sorting_column,
								granularity: $granularity,
								title: $row->title,
								title_base: $row->title_base ?? '',
								created_at: Carbon::parse($row->created_at),
								taken_at: $row->taken_at !== null ? Carbon::parse($row->taken_at) : null,
								is_highlighted: DbBool::parse($row->is_highlighted),
								type: $row->type ?? '',
								rating_avg: $row->rating_avg,
							),
						];
						$processed++;
						$bar->advance();
					}

					DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_id']);
				},
			);

		$bar->finish();
		$this->newLine(2);

		$this->info("Recomputed bucket_id for {$processed} photo_album rows");
		Log::info("Photo bucket backfill completed: {$processed} photo_album rows processed");
	}
}
