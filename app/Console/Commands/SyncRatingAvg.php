<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Models\Statistics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan command to backfill rating_avg column for existing photos.
 *
 * This command syncs the rating_avg values from the statistics table
 * to the photos table for all photos that have ratings.
 *
 * Feature: 009 Rating Ordering and Smart Albums
 * Task: T-009-35
 */
class SyncRatingAvg extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:sync-rating-avg
		{--dry-run : Show what would be updated without making changes}
		{--force : Skip confirmation prompt}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync rating_avg from statistics table to photos table';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$isDryRun = $this->option('dry-run');
		$isForced = $this->option('force');

		$this->info('Syncing rating_avg from statistics to photos...');
		$this->newLine();

		// Count photos that need updating
		$photosToUpdate = DB::table('photos')
			->join('statistics', 'photos.id', '=', 'statistics.photo_id')
			->where('statistics.rating_count', '>', 0)
			->whereRaw('COALESCE(photos.rating_avg, 0) != CAST(statistics.rating_sum AS REAL) / statistics.rating_count')
			->count();

		if ($photosToUpdate === 0) {
			$this->info('✓ All photos are already synchronized.');

			return self::SUCCESS;
		}

		$this->warn("Found {$photosToUpdate} photos with outdated rating_avg values.");

		if ($isDryRun) {
			$this->info('Running in dry-run mode - no changes will be made.');
			$this->showSampleUpdates();

			return self::SUCCESS;
		}

		if (!$isForced && !$this->confirm("Update {$photosToUpdate} photos?", true)) {
			$this->info('Cancelled.');

			return self::SUCCESS;
		}

		$this->newLine();
		$this->info('Updating photos...');

		$bar = $this->output->createProgressBar($photosToUpdate);
		$bar->start();

		$updated = 0;
		$errors = 0;

		// Process in chunks to avoid memory issues
		DB::table('photos')
			->join('statistics', 'photos.id', '=', 'statistics.photo_id')
			->where('statistics.rating_count', '>', 0)
			->whereRaw('COALESCE(photos.rating_avg, 0) != CAST(statistics.rating_sum AS REAL) / statistics.rating_count')
			->select('photos.id', 'statistics.rating_sum', 'statistics.rating_count')
			->orderBy('photos.id')
			->chunk(100, function ($photos) use (&$updated, &$errors, $bar) {
				foreach ($photos as $photo) {
					try {
						$ratingAvg = round($photo->rating_sum / $photo->rating_count, 4);
						DB::table('photos')
							->where('id', $photo->id)
							->update(['rating_avg' => $ratingAvg]);
						$updated++;
					} catch (\Exception $e) {
						$errors++;
						$this->error("\nError updating photo {$photo->id}: {$e->getMessage()}");
					}
					$bar->advance();
				}
			});

		$bar->finish();
		$this->newLine(2);

		if ($errors > 0) {
			$this->error("✗ Updated {$updated} photos with {$errors} errors.");

			return self::FAILURE;
		}

		$this->info("✓ Successfully updated {$updated} photos.");

		return self::SUCCESS;
	}

	/**
	 * Show sample updates in dry-run mode.
	 */
	private function showSampleUpdates(): void
	{
		$samples = DB::table('photos')
			->join('statistics', 'photos.id', '=', 'statistics.photo_id')
			->where('statistics.rating_count', '>', 0)
			->whereRaw('COALESCE(photos.rating_avg, 0) != CAST(statistics.rating_sum AS REAL) / statistics.rating_count')
			->select(
				'photos.id',
				'photos.rating_avg as old_rating',
				'statistics.rating_sum',
				'statistics.rating_count',
				DB::raw('CAST(statistics.rating_sum AS REAL) / statistics.rating_count as new_rating')
			)
			->limit(5)
			->get();

		$this->newLine();
		$this->info('Sample updates (first 5):');
		$this->table(
			['Photo ID', 'Current', 'New', 'Rating Count'],
			$samples->map(fn ($s) => [
				$s->id,
				$s->old_rating ?? 'NULL',
				number_format($s->new_rating, 4, '.', ''),
				$s->rating_count,
			])
		);
	}
}
