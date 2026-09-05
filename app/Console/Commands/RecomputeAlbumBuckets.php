<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Services\AlbumBucketComputer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bulk-recomputes `bucket_id` for every album —
 * sibling to {@see RecomputeAlbumStats}. Run once at initial deploy (backfill),
 * and again if an instance-wide `sorting_albums_col`/`timeline_albums_granularity`/`title_bucket_mode`/
 * `title_bucket_prefix_length` default changes, since those two title
 * configs are instance-wide only and carry no per-album change trigger.
 *
 * Derives every value from the album row itself, plus its resolved
 * parent's sort-column/granularity settings, via one chunked self-join
 * query — never touches `photos`/`photo_album`.
 */
class RecomputeAlbumBuckets extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'lychee:recompute-album-buckets
							{--chunk=1000 : Number of albums to process per batch}';

	/**
	 * @var string
	 */
	protected $description = 'Bulk-recompute the bucket_id column for every album.';

	public function handle(AlbumBucketComputer $bucket_computer): int
	{
		$chunk_size = max(1, (int) $this->option('chunk'));

		$total = DB::table('albums')->count();
		$this->info("Found {$total} albums to process");

		if ($total === 0) {
			$this->info('No albums to process');

			return Command::SUCCESS;
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

		return Command::SUCCESS;
	}
}
