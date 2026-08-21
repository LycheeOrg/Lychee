<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

/**
 * Signs and verifies temporary-link timestamps (Feature 056, DO-056-02).
 *
 * MAC-only: verifies that a timestamp has not been tampered with. TTL and
 * future-timestamp checks are the caller's responsibility (this class has no
 * config/$ttl input) — see GetPhotoAssetRequest.
 *
 * The secret is config('app.key') (NFR-056-01) — no new secret storage.
 */
class TemporaryLinkSigner
{
	/**
	 * Signs the given timestamp, returning a hex HMAC-SHA256 MAC.
	 */
	public function sign(int $timestamp): string
	{
		return hash_hmac('sha256', (string) $timestamp, (string) config('app.key'));
	}

	/**
	 * Verifies that `$mac` is the correct MAC for `$timestamp`.
	 *
	 * Uses `hash_equals()` for a timing-safe comparison.
	 */
	public function verify(int $timestamp, string $mac): bool
	{
		return hash_equals($this->sign($timestamp), $mac);
	}
}
