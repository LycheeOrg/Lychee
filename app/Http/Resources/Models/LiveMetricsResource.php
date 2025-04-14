<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\MetricsAction;
use App\Models\LiveMetrics;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LiveMetricsResource extends Data
{
	public function __construct(
		public string $created_at,
		public string $visitor_id,
		public MetricsAction $action,
		public ?string $photo_id,
		public ?string $photo_title,
		public ?string $album_id,
		public ?string $album_title,
	) {
	}

	/**
	 * @param object{created_at:string,visitor_id:string,action:MetricsAction,photo_id:?string,photo_title:?string,album_id:?string,album_title:?string} $a
	 *
	 * @return LiveMetricsResource
	 */
	public static function fromModel(LiveMetrics $a): LiveMetricsResource
	{
		return new self(
			$a->created_at,
			$a->visitor_id,
			$a->action,
			$a->photo_id,
			$a->photo_title,
			$a->album_id,
			$a->album_title,
		);
	}

}
