<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Repositories\ConfigManager;
use ParagonIE\ConstantTime\Base64 as ParagonieBase64;

/**
 * Signs and verifies temporary-link codes (Feature 056, DO-056-02).
 *
 * A TOTP-like rotating code, but a custom implementation rather than
 * RFC 6238/{@see \PragmaRX\Google2FA\Google2FA}: those follow RFC 4226 §5.3
 * and truncate the HMAC to 31 bits before reducing mod 10^digits, so digits
 * beyond ~10 carry no real added entropy no matter the configured code
 * length. This instead reduces the *full* HMAC-SHA256 digest as a big
 * integer, so every one of {@see self::CODE_DIGITS} digits is real entropy.
 *
 * The time step is `temporary_image_link_life_in_seconds`: the code embeds
 * its own freshness (current step, plus a one-step grace window so a code
 * doesn't go stale right at rotation), so unlike the previous raw-timestamp
 * MAC, the caller needs no separate timestamp header or TTL check.
 *
 * The secret is config('app.key') (NFR-056-01) — no new secret storage.
 */
class TemporaryLinkSigner
{
	private const CODE_DIGITS = 12;
	private const GRACE_STEPS_BACK = 1;

	/**
	 * The current, valid rotating code.
	 */
	public function sign(): string
	{
		return $this->codeForCounter($this->currentCounter());
	}

	/**
	 * Verifies `$code` against the current time step and a small grace
	 * window of previous steps.
	 *
	 * Uses `hash_equals()` for a timing-safe comparison.
	 */
	public function verify(string $code): bool
	{
		$current = $this->currentCounter();

		for ($back = 0; $back <= self::GRACE_STEPS_BACK; $back++) {
			if (hash_equals($this->codeForCounter($current - $back), $code)) {
				return true;
			}
		}

		return false;
	}

	private function currentCounter(): int
	{
		$step_seconds = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');

		return intdiv(now()->timestamp, $step_seconds);
	}

	/**
	 * Computes the code for a given step counter: the full HMAC-SHA256
	 * digest of the (big-endian, 8-byte) counter — HOTP's counter encoding,
	 * see RFC 4226 §5.2 — keyed by {@see self::getKey()}, reduced mod
	 * 10^{@see self::CODE_DIGITS} as a big integer (via bcmath, byte by
	 * byte, since `ext-gmp` isn't a project dependency) rather than
	 * RFC 4226's 31-bit truncation.
	 */
	private function codeForCounter(int $counter): string
	{
		$counter_bytes = pack('J', $counter);
		$hmac = hash_hmac('sha256', $counter_bytes, $this->getKey(), true);

		$modulus = bcpow('10', (string) self::CODE_DIGITS);
		$remainder = '0';
		foreach (str_split($hmac) as $byte) {
			$remainder = bcmod(bcadd(bcmul($remainder, '256'), (string) ord($byte)), $modulus);
		}

		return str_pad($remainder, self::CODE_DIGITS, '0', STR_PAD_LEFT);
	}

	/**
	 * Derives the raw HMAC key from `config('app.key')`, decoding its
	 * `base64:` prefix the same way Laravel's own encrypter does.
	 */
	private function getKey(): string
	{
		$key = (string) config('app.key');

		return str_starts_with($key, 'base64:') ? ParagonieBase64::decode(substr($key, 7)) : $key;
	}
}
