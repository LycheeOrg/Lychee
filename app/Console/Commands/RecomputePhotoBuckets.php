<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Assets\DbBool;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Services\PhotoBucketComputer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bulk-recomputes `bucket_id` for every `photo_album` row — sibling to
 * {@see \App\Console\Commands\RecomputeAlbumBuckets}. Run once at initial
 * deploy (backfill), and again if an instance-wide `sorting_photos_col`/
 * `timeline_photos_granularity`/`photo_title_bucket_mode`/
 * `photo_title_bucket_prefix_length` default changes, since those two title
 * configs are instance-wide only and carry no per-album change trigger.
 *
 * Derives every value from the `photo_album` row's own `photo_id`/`album_id`
 * pair's already-loaded photo columns plus the containing album's resolved
 * sorting/timeline settings, via one chunked self-join query — never
 * touches `size_variants`/`tags`.
 */
class RecomputePhotoBuckets extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'lychee:recompute-photo-buckets
							{--chunk=1000 : Number of photo_album rows to process per batch}';

	/**
	 * @var string
	 */
	protected $description = 'Bulk-recompute the bucket_id column for every photo_album row.';

	public function handle(PhotoBucketComputer $bucket_computer): int
	{
		$chunk_size = max(1, (int) $this->option('chunk'));

		$total = DB::table('photo_album')->count();
		$this->info("Found {$total} photo_album rows to process");

		if ($total === 0) {
			$this->info('No photo_album rows to process');

			return Command::SUCCESS;
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

		return Command::SUCCESS;
	}
}
