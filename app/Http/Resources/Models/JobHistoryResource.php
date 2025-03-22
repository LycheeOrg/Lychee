<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\JobHistory;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class JobHistoryResource extends Data
{
	public string $username;
	#[LiteralTypeScriptType("'ready'|'success'|'failure'|'started'")]
	public string $status;
	public string $created_at;
	public string $updated_at;
	public string $job;

	public function __construct(JobHistory $job_history)
	{
		$this->username = $job_history->owner->username;
		$this->status = $job_history->status->name();
		$this->created_at = $job_history->created_at->toIso8601String();
		$this->updated_at = $job_history->updated_at->toIso8601String();
		$this->job = $job_history->job;
	}

	public static function fromModel(JobHistory $job_history): JobHistoryResource
	{
		return new self($job_history);
	}
}