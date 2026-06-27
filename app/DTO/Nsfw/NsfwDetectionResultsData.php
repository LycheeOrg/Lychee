<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Nsfw;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwDetectionResultsData extends Data
{
	/**
	 * @param array<int,NsfwDetectionItemData> $all_detected
	 * @param array<int,NsfwDetectionItemData> $block_detected
	 * @param array<int,NsfwDetectionItemData> $review_detected
	 * @param array<int,NsfwDetectionItemData> $sensitive_detected
	 */
	public function __construct(
		public string $photo_id,
		public string $status,
		#[LiteralTypeScriptType('boolean')]
		public Optional|bool $should_block = false,
		#[LiteralTypeScriptType('boolean')]
		public Optional|bool $should_review = false,
		#[LiteralTypeScriptType('boolean')]
		public Optional|bool $is_sensitive = false,
		#[DataCollectionOf(NsfwDetectionItemData::class)]
		#[LiteralTypeScriptType('App.DTO.Nsfw.NsfwDetectionItemData[]')]
		public Optional|array $all_detected = [],
		#[DataCollectionOf(NsfwDetectionItemData::class)]
		#[LiteralTypeScriptType('App.DTO.Nsfw.NsfwDetectionItemData[]')]
		public Optional|array $block_detected = [],
		#[DataCollectionOf(NsfwDetectionItemData::class)]
		#[LiteralTypeScriptType('App.DTO.Nsfw.NsfwDetectionItemData[]')]
		public Optional|array $review_detected = [],
		#[DataCollectionOf(NsfwDetectionItemData::class)]
		#[LiteralTypeScriptType('App.DTO.Nsfw.NsfwDetectionItemData[]')]
		public Optional|array $sensitive_detected = [],
		public ?string $error_code = null,
		public ?string $message = null,
	) {
	}
}
