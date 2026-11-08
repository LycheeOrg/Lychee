<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Auth;

use App\DTO\BlockedIp;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use function Safe\inet_ntop;
use function Safe\inet_pton;

/**
 * Login throttle of `POST /api/v2/Auth::login` (Feature 112, ADR-112-01).
 *
 * Counts attempts per IP in the rate limiter's cache store and keeps one
 * cache entry listing the blocked IPs, so the operator can list and unban
 * them from the console. The entry is written only by the attempt that
 * reaches the limit: attempts below the limit and blocked requests never
 * touch it, and nothing here uses the database (NFR-112-01).
 */
final class LoginThrottleService
{
	public const MAX_ATTEMPTS = 10;
	public const DECAY_SECONDS = 3600;
	public const INDEX_KEY = 'login-throttle:blocked';
	public const LOCK_KEY = 'login-throttle:blocked:lock';
	public const INDEX_CAP = 1000;

	private const KEY_PREFIX = 'login-throttle:';
	private const LOCK_SECONDS = 5;
	private const REQUEST_LOCK_WAIT = 1;
	private const CONSOLE_LOCK_WAIT = 5;

	private Repository $store;
	private RateLimiter $limiter;

	public function __construct()
	{
		$this->store = Cache::store($this->configuredStore());
		$this->limiter = new RateLimiter($this->store);
	}

	/**
	 * Canonical form of an IP address, or null when the value is not one.
	 *
	 * @param string $ip
	 *
	 * @return string|null
	 */
	public static function normalizeIp(string $ip): ?string
	{
		if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
			return null;
		}

		return inet_ntop(inet_pton($ip));
	}

	/**
	 * Drop the blocks that have ended.
	 *
	 * @param array<string,int> $index ip → unix time at which the block ends
	 * @param int               $now   unix time
	 *
	 * @return array<string,int>
	 */
	public static function prune(array $index, int $now): array
	{
		return array_filter($index, fn (int $until) => $until > $now);
	}

	/**
	 * Keep the `$max` latest block ends, latest first.
	 *
	 * @param array<string,int> $index ip → unix time at which the block ends
	 * @param int               $max
	 *
	 * @return array<string,int>
	 */
	public static function cap(array $index, int $max): array
	{
		arsort($index);

		return array_slice($index, 0, $max, true);
	}

	/**
	 * Name of the cache store holding the counters.
	 */
	public function storeName(): string
	{
		return $this->configuredStore() ?? strval(Config::get('cache.default'));
	}

	/**
	 * Whether the store supports locks, which the blocked-IP index needs.
	 */
	public function hasLocks(): bool
	{
		return $this->store->getStore() instanceof LockProvider;
	}

	public function isBlocked(string $ip): bool
	{
		return $this->limiter->tooManyAttempts($this->key($ip), self::MAX_ATTEMPTS);
	}

	/**
	 * Count one attempt, and index the IP when this attempt reaches the limit.
	 *
	 * @return int attempts in the current window
	 */
	public function hit(string $ip): int
	{
		$attempts = $this->limiter->hit($this->key($ip), self::DECAY_SECONDS);
		if ($attempts === self::MAX_ATTEMPTS) {
			$this->index($ip);
		}

		return $attempts;
	}

	/**
	 * Headers of a response that passed the throttle.
	 *
	 * @return array<string,int>
	 */
	public function passHeaders(string $ip): array
	{
		return [
			'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
			'X-RateLimit-Remaining' => $this->limiter->remaining($this->key($ip), self::MAX_ATTEMPTS),
		];
	}

	/**
	 * Headers of the 429 response sent to a blocked IP.
	 *
	 * @return array<string,int>
	 */
	public function blockedHeaders(string $ip): array
	{
		$retry_after = $this->limiter->availableIn($this->key($ip));

		return [
			'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
			'X-RateLimit-Remaining' => 0,
			'Retry-After' => $retry_after,
			'X-RateLimit-Reset' => Carbon::now()->getTimestamp() + $retry_after,
		];
	}

	/**
	 * Indexed IPs that are still blocked, latest block end first.
	 *
	 * @return list<BlockedIp>
	 */
	public function blocked(): array
	{
		$now = Carbon::now()->getTimestamp();

		return collect(array_keys($this->readIndex()))
			->map(fn ($ip) => strval($ip))
			->filter(fn (string $ip) => $this->isBlocked($ip))
			->map(fn (string $ip) => new BlockedIp(
				ip: $ip,
				attempts: $this->limiter->attempts($this->key($ip)),
				blocked_until: $now + $this->limiter->availableIn($this->key($ip)),
			))
			->sortByDesc(fn (BlockedIp $blocked_ip) => $blocked_ip->blocked_until)
			->values()
			->all();
	}

	/**
	 * Clear the IP's counter and its index entry.
	 *
	 * @return bool whether the IP was blocked
	 */
	public function unban(string $ip): bool
	{
		$ip = self::normalizeIp($ip) ?? $ip;
		$was_blocked = $this->isBlocked($ip);
		$this->limiter->clear($this->key($ip));
		$this->removeFromIndex($ip);
		if ($was_blocked) {
			Log::channel('login')->notice('IP ' . $ip . ' unbanned from the console');
		}

		return $was_blocked;
	}

	private function configuredStore(): ?string
	{
		$name = Config::get('cache.limiter');

		return is_string($name) ? $name : null;
	}

	private function key(string $ip): string
	{
		return self::KEY_PREFIX . (self::normalizeIp($ip) ?? $ip);
	}

	/**
	 * @return array<string,int>
	 */
	private function readIndex(): array
	{
		$index = $this->store->get(self::INDEX_KEY);

		return is_array($index) ? $index : [];
	}

	/**
	 * Add the IP to the index (FR-112-02). A missed lock only costs the list
	 * entry: the IP stays blocked and can still be unbanned by address.
	 */
	private function index(string $ip): void
	{
		$canonical = self::normalizeIp($ip);
		$store = $this->store->getStore();
		if ($canonical === null || !$store instanceof LockProvider) {
			return;
		}

		$until = Carbon::now()->addSeconds($this->limiter->availableIn($this->key($canonical)));
		try {
			$store->lock(self::LOCK_KEY, self::LOCK_SECONDS)->block(
				self::REQUEST_LOCK_WAIT,
				fn () => $this->writeIndex(fn (array $index) => self::cap([...$index, $canonical => $until->getTimestamp()], self::INDEX_CAP)),
			);
		} catch (LockTimeoutException) {
			Log::channel('login')->warning('Blocked-IP list not updated for ' . $canonical . ': lock timeout');

			return;
		}
		Log::channel('login')->notice('IP ' . $canonical . ' blocked from login until ' . $until->toIso8601String());
	}

	/**
	 * Remove the IP from the index. A missed lock leaves a stale entry, which
	 * {@see blocked()} skips because its counter is gone.
	 */
	private function removeFromIndex(string $ip): void
	{
		$store = $this->store->getStore();
		if (!$store instanceof LockProvider) {
			return;
		}

		try {
			$store->lock(self::LOCK_KEY, self::LOCK_SECONDS)->block(
				self::CONSOLE_LOCK_WAIT,
				fn () => $this->writeIndex(fn (array $index) => array_diff_key($index, [$ip => true])),
			);
		} catch (LockTimeoutException) {
			// The counter is cleared already; the stale entry is not listed.
		}
	}

	/**
	 * Apply `$change` to the pruned index and store it for one window.
	 *
	 * @param \Closure(array<string,int>): array<string,int> $change
	 */
	private function writeIndex(\Closure $change): void
	{
		$index = $change(self::prune($this->readIndex(), Carbon::now()->getTimestamp()));
		$this->store->put(self::INDEX_KEY, $index, self::DECAY_SECONDS);
	}
}
