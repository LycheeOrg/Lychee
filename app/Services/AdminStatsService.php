<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\AdminStatsOverview;
use App\Enum\JobStatus;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminStatsService
{
	public function getOverview(bool $force = false): AdminStatsOverview
	{
		if ($force) {
			Cache::forget('admin.stats');
		}

		$cached = Cache::get('admin.stats');
		if ($cached instanceof AdminStatsOverview) {
			return $cached;
		}

		$errors = [];

		$photos_count = 0;
		try {
			$photos_count = Photo::count();
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to count photos: ' . $e->getMessage());
			$errors[] = 'Failed to count photos: ' . $e->getMessage();
		}

		$albums_count = 0;
		try {
			$albums_count = Album::count();
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to count albums: ' . $e->getMessage());
			$errors[] = 'Failed to count albums: ' . $e->getMessage();
		}

		$users_count = 0;
		try {
			$users_count = User::count();
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to count users: ' . $e->getMessage());
			$errors[] = 'Failed to count users: ' . $e->getMessage();
		}

		$storage_bytes = 0;
		try {
			$storage_bytes = (int) SizeVariant::sum('filesize');
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to sum storage: ' . $e->getMessage());
			$errors[] = 'Failed to sum storage: ' . $e->getMessage();
		}

		$queued_jobs = 0;
		try {
			$queue_connection = Config::get('queue.default', 'sync');
			if ($queue_connection === 'database') {
				$queued_jobs = DB::table('jobs')->count();
			}
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to count queued jobs: ' . $e->getMessage());
			$errors[] = 'Failed to count queued jobs: ' . $e->getMessage();
		}

		$failed_jobs_24h = 0;
		try {
			$failed_jobs_24h = JobHistory::where('status', JobStatus::FAILURE)
				->where('updated_at', '>=', now()->subDay())
				->count();
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to count failed jobs: ' . $e->getMessage());
			$errors[] = 'Failed to count failed jobs: ' . $e->getMessage();
		}

		$last_successful_job_at = null;
		try {
			$last_successful_job_at = JobHistory::where('status', JobStatus::SUCCESS)->max('updated_at');
		} catch (\Throwable $e) {
			Log::warning('AdminStatsService: failed to get last successful job: ' . $e->getMessage());
			$errors[] = 'Failed to get last successful job: ' . $e->getMessage();
		}

		$overview = new AdminStatsOverview(
			photos_count: $photos_count,
			albums_count: $albums_count,
			users_count: $users_count,
			storage_bytes: $storage_bytes,
			queued_jobs: $queued_jobs,
			failed_jobs_24h: $failed_jobs_24h,
			last_successful_job_at: $last_successful_job_at,
			cached_at: now()->toIso8601String(),
			errors: $errors,
		);

		if ($errors === []) {
			Cache::put('admin.stats', $overview, 300);
		}

		return $overview;
	}
}
