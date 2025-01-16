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

	public function __construct(JobHistory $jobHistory)
	{
		$this->username = $jobHistory->owner->username;
		$this->status = $jobHistory->status->name();
		$this->created_at = $jobHistory->created_at->toIso8601String();
		$this->updated_at = $jobHistory->updated_at->toIso8601String();
		$this->job = $jobHistory->job;
	}

	public static function fromModel(JobHistory $jobHistory): JobHistoryResource
	{
		return new self($jobHistory);
	}
}