<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Insights;

/**
 * Whose photos Insights cover (Feature 085, DO-085-01, ADR-085-02): one
 * owner, every owner when `owner_id` is null, or every photo of one album
 * tree (the album and its descendants, nested-set bounds `album_left` …
 * `album_right`).
 */
final readonly class InsightsScope
{
	private function __construct(
		public ?int $owner_id,
		public ?string $album_id = null,
		public int $album_left = 0,
		public int $album_right = 0,
	) {
	}

	public static function owner(int $owner_id): self
	{
		return new self($owner_id);
	}

	public static function wholeInstance(): self
	{
		return new self(null);
	}

	public static function album(string $album_id, int $left, int $right): self
	{
		return new self(null, $album_id, $left, $right);
	}

	public function isAlbum(): bool
	{
		return $this->album_id !== null;
	}

	public function cacheKey(): string
	{
		return match (true) {
			$this->album_id !== null => 'album-' . $this->album_id,
			$this->owner_id !== null => 'owner-' . $this->owner_id,
			default => 'all',
		};
	}
}
