<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\DTO\AdminStatsOverview;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class AdminStatsResource extends Data
{
	public int $photos_count;
	public int $albums_count;
	public int $users_count;
	public int $storage_bytes;
	public int $queued_jobs;
	public int $failed_jobs_24h;
	public ?string $last_successful_job_at;
	public string $cached_at;
	/** @var string[] */
	public array $errors;

	public static function fromOverview(AdminStatsOverview $o): self
	{
		$resource = new self();
		$resource->photos_count = $o->photos_count;
		$resource->albums_count = $o->albums_count;
		$resource->users_count = $o->users_count;
		$resource->storage_bytes = $o->storage_bytes;
		$resource->queued_jobs = $o->queued_jobs;
		$resource->failed_jobs_24h = $o->failed_jobs_24h;
		$resource->last_successful_job_at = $o->last_successful_job_at;
		$resource->cached_at = $o->cached_at;
		$resource->errors = $o->errors;

		return $resource;
	}
}
