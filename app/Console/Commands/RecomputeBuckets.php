<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Events\AlbumListingCacheFlushRequested;
use App\Events\PhotoListingCacheFlushRequested;
use App\Services\BucketKeyWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Rewrites `bucket_key` of every album, then of every `photo_album` row,
 * in the granularity-free format (FR-092-15), chunked by
 * {@see BucketKeyWriter} (FR-092-14), flushing the album, then the photo
 * listings once their keys are written.
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
	protected $description = 'Bulk-recompute the bucket_key column for every album and every photo_album row.';

	public function handle(BucketKeyWriter $writer): int
	{
		$chunk_size = max(1, (int) $this->option('chunk'));

		$this->recompute('albums', DB::table('albums')->count(), fn (\Closure $on_chunk): int => $writer->writeAlbumKeys(fn ($query) => $query, $chunk_size, $on_chunk));
		AlbumListingCacheFlushRequested::dispatch();
		$this->newLine();
		$this->recompute('photo_album rows', DB::table('photo_album')->count(), fn (\Closure $on_chunk): int => $writer->writePhotoAlbumKeys(fn ($query) => $query, $chunk_size, $on_chunk));
		PhotoListingCacheFlushRequested::dispatch();

		return Command::SUCCESS;
	}

	/**
	 * @param \Closure(\Closure(Collection<int,object>):void):int $write
	 */
	private function recompute(string $label, int $total, \Closure $write): void
	{
		$this->info("Found {$total} {$label} to process");
		if ($total === 0) {
			$this->info("No {$label} to process");

			return;
		}

		$bar = $this->output->createProgressBar($total);
		$bar->start();
		$processed = $write(fn (Collection $rows) => $bar->advance($rows->count()));
		$bar->finish();
		$this->newLine(2);

		$this->info("Recomputed bucket_key for {$processed} {$label}");
		Log::info("Bucket recompute completed: {$processed} {$label} processed");
	}
}
