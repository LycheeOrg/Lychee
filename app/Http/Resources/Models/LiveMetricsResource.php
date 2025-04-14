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
		public ?string $album_id,
		public ?string $title,
		public ?string $url = null,
	) {
	}

	/**
	 * @return LiveMetricsResource
	 */
	public static function fromModel(LiveMetrics $a): LiveMetricsResource
	{
		$title = $a->photo_id !== null ? $a->photo->title : $a->album_impl->title;
		$url = $a->photo_id !== null ? $a->photo->size_variants?->getSmall()?->url ?? $a->photo->size_variants?->getThumb()?->url : $a->album?->thumb?->thumbUrl;

		return new self(
			created_at: $a->created_at,
			visitor_id: $a->visitor_id,
			action: $a->action,
			photo_id: $a->photo_id,
			album_id: $a->album_id ?? $a->photo->album_id,
			title: $title,
			url: $url,
		);
	}
}
