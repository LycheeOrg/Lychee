<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\AlbumSizeStatistics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes size statistics for a single album and propagates to parent.
 *
 * Queries size_variants for all photos in the album (direct children only),
 * groups by variant type, sums filesize, and stores in album_size_statistics table.
 * Excludes PLACEHOLDER variants from all calculations.
 */
class RecomputeAlbumSizeJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	private string $jobId;

	/**
	 * Number of times to retry the job.
	 *
	 * @var int
	 */
	public $tries = 3;

	/**
	 * @param string $album_id The ID of the album to recompute
	 */
	public function __construct(
		public string $album_id,
		public bool $propagate_to_parent = true,
	) {
		$this->jobId = uniqid('job_', true);

		// Register this as the latest job for this album
		Cache::put(
			'album_size_latest_job:' . $this->album_id,
			$this->jobId,
			ttl: now()->plus(days: 1)
		);
	}

	/**
	 * Get the middleware the job should pass through.
	 *
	 * @return array<int,object>
	 */
	public function middleware(): array
	{
		return [
			Skip::when(fn () => $this->hasNewerJobQueued()),
		];
	}

	protected function hasNewerJobQueued(): bool
	{
		$cache_key = 'album_size_latest_job:' . $this->album_id;
		$latest_job_id = Cache::get($cache_key);

		// We skip if there is a newer job queued (latest job ID is different from this one)
		$has_newer_job = $latest_job_id !== null && $latest_job_id !== $this->jobId;
		if ($has_newer_job) {
			Log::channel('jobs')->debug("Skipping job {$this->jobId} for album {$this->album_id} due to newer job {$latest_job_id} queued.");
		}

		return $has_newer_job;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Log::channel('jobs')->info("Recomputing sizes for album {$this->album_id} (job {$this->jobId})");
		Cache::forget("album_size_latest_job:{$this->album_id}");

		try {
			// Fetch the album
			$album = Album::where('id', '=', $this->album_id)->first();
			if ($album === null) {
				Log::warning("Album {$this->album_id} not found, skipping recompute.");

				return;
			}

			// Compute sizes by querying size_variants for photos in this album (direct children only)
			// Exclude PLACEHOLDER (type 7) from all size calculations
			$sizes = $this->computeSizes($album);

			// Update or create statistics row
			AlbumSizeStatistics::updateOrCreate(
				['album_id' => $album->id],
				$sizes
			);

			Log::channel('jobs')->debug("Updated size statistics for album {$album->id}");

			// Propagate to parent if exists
			if ($album->parent_id !== null && $this->propagate_to_parent) {
				Log::channel('jobs')->debug("Propagating to parent {$album->parent_id}");
				self::dispatch($album->parent_id);
			}
		} catch (\Exception $e) {
			Log::channel('jobs')->error("Propagation stopped at album {$this->album_id} due to failure: " . $e->getMessage());

			throw $e;
		}
	}

	/**
	 * Compute size breakdown for all variant types.
	 *
	 * Queries size_variants for photos directly in this album (NOT descendants),
	 * groups by variant type, sums filesize. Excludes PLACEHOLDER (type 7).
	 *
	 * @param Album $album
	 *
	 * @return array<string,int> Array with keys: size_raw, size_thumb, size_thumb2x, size_small, size_small2x, size_medium, size_medium2x, size_original
	 */
	private function computeSizes(Album $album): array
	{
		// Initialize all sizes to 0
		$sizes = [
			'size_raw' => 0,
			'size_thumb' => 0,
			'size_thumb2x' => 0,
			'size_small' => 0,
			'size_small2x' => 0,
			'size_medium' => 0,
			'size_medium2x' => 0,
			'size_original' => 0,
		];

		// Query size_variants for photos in this album
		// JOIN: size_variants -> photos -> photo_album
		// Filter by album_id, exclude PLACEHOLDER
		// Group by type, SUM filesize
		$results = DB::table('size_variants')
			->join('photos', 'size_variants.photo_id', '=', 'photos.id')
			->join(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
			->where(PA::ALBUM_ID, '=', $album->id)
			->where('size_variants.type', '!=', SizeVariantType::PLACEHOLDER->value)
			->select('size_variants.type', DB::raw('SUM(size_variants.filesize) as total_size'))
			->groupBy('size_variants.type')
			->get();

		// Map results to size array
		foreach ($results as $row) {
			$type = SizeVariantType::from($row->type);
			$column_name = 'size_' . $type->name();
			if (isset($sizes[$column_name])) {
				$sizes[$column_name] = (int) $row->total_size;
			}
		}

		return $sizes;
	}

	/**
	 * Handle job failure after all retries exhausted.
	 *
	 * @param \Throwable $exception
	 *
	 * @return void
	 */
	public function failed(\Throwable $exception): void
	{
		Log::channel('jobs')->error("Job failed permanently for album {$this->album_id}: " . $exception->getMessage());
		// Do NOT dispatch parent job on failure - propagation stops here
	}
}
