<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\PhotoRating;
use App\Models\Statistics;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoRatingResource extends Data
{
	public function __construct(
		public int $rating_user = 0,
		public int $rating_count = 0,
		public float $rating_avg = 0,
	) {
	}

	public static function fromModel(
		Statistics $stats,
		PhotoRating|null $rating,
		ConfigManager $config_manager,
	): PhotoRatingResource|null {
		// If user is not authenticated
		// We return rating stats only (we are already allowed to see them)
		if (Auth::guest()) {
			return new self(
				0,
				$stats->rating_count,
				$stats->rating_avg,
			);
		}

		// User is logged in.
		if ($rating === null && $config_manager->getValueAsBool('rating_show_only_when_user_rated')) {
			// If rating is null, user did not rate the photo => hide the values with 0.
			return new self(
				0,
				0,
				0,
			);
		}

		return new self(
			$rating?->rating ?? 0,
			$stats->rating_count,
			$stats->rating_avg,
		);
	}
}
