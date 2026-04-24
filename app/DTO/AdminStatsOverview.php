<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Immutable value object returned by AdminStatsService.
 */
class AdminStatsOverview
{
	public function __construct(
		public readonly int $photos_count,
		public readonly int $albums_count,
		public readonly int $users_count,
		public readonly int $storage_bytes,
		public readonly int $queued_jobs,
		public readonly int $failed_jobs_24h,
		public readonly ?string $last_successful_job_at,
		public readonly string $cached_at,
		/** @var string[] */
		public readonly array $errors,
	) {
	}
}
