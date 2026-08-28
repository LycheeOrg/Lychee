<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Carries the metadata to embed into a photo's Original/RAW file.
 *
 * Built fresh from the current {@link \App\Models\Photo} state at job-execution
 * time (never from a value captured at dispatch time), see EmbedMetadataJob.
 */
final readonly class MetadataWritePayload
{
	/**
	 * @param ?string  $title       null/empty clears the embedded title tags
	 * @param ?string  $description null/empty clears the embedded description tags
	 * @param string[] $tags        empty array clears the embedded keyword/subject tags
	 * @param ?int     $rating      1-5, or null when the owner has no rating row
	 *                              (clears the embedded rating tags) — never a
	 *                              literal 0, since `photo_ratings.rating` is
	 *                              DB-constrained to 1-5
	 */
	public function __construct(
		public ?string $title,
		public ?string $description,
		public array $tags,
		public ?int $rating,
	) {
	}
}
