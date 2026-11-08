<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\DTO\BlockedIp;
use App\Services\Auth\LoginThrottleService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * List the IP addresses blocked from logging in, or unban one (Feature 112).
 *
 * Usage: php artisan lychee:blocked-ips [--unban=<ip>]
 *
 * With the file cache, run it as the web server user so it reads and
 * deletes the same cache files.
 */
class BlockedIps extends Command
{
	protected $signature = 'lychee:blocked-ips {--unban= : IP address to unban}';

	protected $description = 'List the IP addresses blocked from logging in, or unban one.';

	public function handle(LoginThrottleService $throttle): int
	{
		if (!$throttle->hasLocks()) {
			$this->error(sprintf(
				"The login throttle uses the cache store '%s', which has no locks (for example apc): blocked IPs cannot be listed or unbanned from the console.",
				$throttle->storeName(),
			));

			return self::FAILURE;
		}

		$unban = $this->option('unban');
		if (is_string($unban)) {
			return $this->unban($throttle, $unban);
		}

		return $this->list($throttle);
	}

	private function unban(LoginThrottleService $throttle, string $value): int
	{
		$ip = LoginThrottleService::normalizeIp($value);
		if ($ip === null) {
			$this->error($value . ' is not a valid IP address.');

			return self::INVALID;
		}

		$this->info($throttle->unban($ip) ? 'Unbanned ' . $ip . '.' : $ip . ' is not blocked.');

		return self::SUCCESS;
	}

	private function list(LoginThrottleService $throttle): int
	{
		$blocked = $throttle->blocked();
		if ($blocked === []) {
			$this->info('No blocked IP.');

			return self::SUCCESS;
		}

		$now = Carbon::now()->getTimestamp();
		$timezone = strval(Config::get('app.timezone'));
		$this->table(
			['IP', 'Attempts', 'Blocked until', 'Minutes left'],
			array_map(fn (BlockedIp $blocked_ip) => [
				$blocked_ip->ip,
				$blocked_ip->attempts,
				Carbon::createFromTimestamp($blocked_ip->blocked_until, $timezone)->format('Y-m-d H:i:s T'),
				intval(ceil(($blocked_ip->blocked_until - $now) / 60)),
			], $blocked),
		);

		return self::SUCCESS;
	}
}
