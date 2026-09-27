<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;
use function Safe\json_encode;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Decides whether the gallery password locks the current visitor out.
 *
 * The unlock token lives in an encrypted cookie holding an HMAC-SHA3-256
 * fingerprint of the stored password hash plus an optional expiry.
 * bcrypt is never used here: verifying the password itself only happens
 * when the visitor unlocks the gallery.
 */
final class GalleryLockState
{
	public const CONFIG_KEY = 'gallery_password';
	public const LIFETIME_CONFIG_KEY = 'gallery_password_cookie_lifetime';
	public const COOKIE_NAME = 'lychee_gallery_unlock';

	/**
	 * Whether a gallery password is configured.
	 */
	public static function isPasswordSet(ConfigManager $configs): bool
	{
		return $configs->getValueAsString(self::CONFIG_KEY) !== '';
	}

	/**
	 * RSS feeds are disabled for everyone while a gallery password is set.
	 */
	public static function isRssEnabled(ConfigManager $configs): bool
	{
		return $configs->getValueAsBool('rss_enable') && !self::isPasswordSet($configs);
	}

	/**
	 * Album embeds are disabled for everyone while a gallery password is set.
	 */
	public static function isEmbedEnabled(ConfigManager $configs): bool
	{
		return $configs->getValueAsBool('is_embed_enabled') && !self::isPasswordSet($configs);
	}

	/**
	 * Whether the current request is locked out by the gallery password.
	 */
	public static function isLockedForRequest(Request $request): bool
	{
		$cookie_value = $request->cookie(self::COOKIE_NAME);

		return self::isLocked(
			$request->configs()->getValueAsString(self::CONFIG_KEY),
			Auth::check(),
			is_string($cookie_value) ? $cookie_value : null,
			time(),
		);
	}

	/**
	 * Pure lock predicate.
	 *
	 * @param string      $stored_hash  bcrypt hash of the gallery password, '' when disabled
	 * @param bool        $is_logged_in whether the visitor is authenticated
	 * @param string|null $cookie_value decrypted value of the unlock cookie
	 * @param int         $now          current unix timestamp
	 */
	public static function isLocked(#[\SensitiveParameter] string $stored_hash, bool $is_logged_in, ?string $cookie_value, int $now): bool
	{
		if ($stored_hash === '' || $is_logged_in) {
			return false;
		}

		return !self::isValidCookie($stored_hash, $cookie_value, $now);
	}

	/**
	 * Keyed fingerprint of the stored password hash.
	 */
	public static function fingerprint(#[\SensitiveParameter] string $stored_hash): string
	{
		return hash_hmac('sha3-256', $stored_hash, strval(config('app.key')));
	}

	/**
	 * Value stored in the unlock cookie.
	 *
	 * @param string   $stored_hash bcrypt hash of the gallery password
	 * @param int|null $expires_at  unix timestamp, null for a browser-session cookie
	 */
	public static function makeCookieValue(#[\SensitiveParameter] string $stored_hash, ?int $expires_at): string
	{
		return json_encode(['f' => self::fingerprint($stored_hash), 'exp' => $expires_at]);
	}

	/**
	 * Unlock cookie handed out by `Gallery::unlock`.
	 *
	 * @param string $stored_hash   bcrypt hash of the gallery password
	 * @param int    $lifetime_days 0 for a browser-session cookie
	 * @param bool   $secure        whether the request came over HTTPS
	 * @param int    $now           current unix timestamp
	 */
	public static function makeUnlockCookie(#[\SensitiveParameter] string $stored_hash, int $lifetime_days, bool $secure, int $now): Cookie
	{
		$expires_at = $lifetime_days === 0 ? null : $now + $lifetime_days * 86400;

		return cookie(
			name: self::COOKIE_NAME,
			value: self::makeCookieValue($stored_hash, $expires_at),
			minutes: $lifetime_days * 1440,
			path: '/',
			secure: $secure,
			httpOnly: true,
			sameSite: 'lax',
		);
	}

	private static function isValidCookie(#[\SensitiveParameter] string $stored_hash, ?string $cookie_value, int $now): bool
	{
		$payload = self::decodeCookie($cookie_value);
		if ($payload === null) {
			return false;
		}

		$is_expired = $payload['exp'] !== null && $payload['exp'] <= $now;

		return !$is_expired && hash_equals(self::fingerprint($stored_hash), $payload['f']);
	}

	/**
	 * @return array{f:string,exp:int|null}|null
	 */
	private static function decodeCookie(?string $cookie_value): ?array
	{
		try {
			$decoded = json_decode($cookie_value ?? '', true);
		} catch (JsonException) {
			return null;
		}
		if (!is_array($decoded) || !is_string($decoded['f'] ?? null)) {
			return null;
		}

		$exp = $decoded['exp'] ?? null;
		if ($exp !== null && !is_int($exp)) {
			return null;
		}

		return ['f' => $decoded['f'], 'exp' => $exp];
	}
}
