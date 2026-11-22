<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Sitemap;

use Illuminate\Support\Carbon;

/**
 * `<lastmod>` of an album in the sitemap (Feature 117, FR-117-12).
 *
 * The later of the album's own last edit and the newest capture date of the
 * photos in it and below it. A capture date in the future (wrong camera clock)
 * is ignored. Both columns are stored in UTC.
 */
final class SitemapLastmod
{
	/**
	 * @param string|null $updated_at   `base_albums.updated_at` as stored
	 * @param string|null $max_taken_at `albums.max_taken_at` as stored
	 * @param int         $now          generation time (unix timestamp)
	 *
	 * @return string|null W3C datetime in UTC, or null when no date applies
	 */
	public function compute(?string $updated_at, ?string $max_taken_at, int $now): ?string
	{
		$updated = $this->parse($updated_at);
		$taken = $this->parse($max_taken_at);
		if ($taken !== null && $taken->getTimestamp() > $now) {
			$taken = null;
		}

		$latest = $updated === null || ($taken !== null && $taken->greaterThan($updated)) ? $taken : $updated;

		return $latest?->toW3cString();
	}

	private function parse(?string $value): ?Carbon
	{
		return $value === null ? null : Carbon::parse($value, 'UTC')->startOfSecond();
	}
}
