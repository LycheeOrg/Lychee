<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Json;

use App\Contracts\ExternalRequest;
use App\Exceptions\Internal\RequestFailedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use function Safe\file_get_contents;
use function Safe\ini_get;

class ExternalRequestFunctions implements ExternalRequest
{
	protected ?string $data = null;

	public function __construct(
		private string $url,
		private int $ttl_in_days,
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function clear_cache(): void
	{
		Cache::forget($this->url);
		Cache::forget($this->url . '_age');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_age_text(): string
	{
		$age = Cache::get($this->url . '_age');
		if (!$age instanceof \DateTimeInterface) {
			// @codeCoverageIgnoreStart
			return 'unknown';
			// @codeCoverageIgnoreEnd
		}
		try {
			$text = match (0) {
				(int) now()->diffInMinutes($age) => (int) -now()->diffInSeconds($age) . ' seconds',
				// @codeCoverageIgnoreStart
				(int) now()->diffInHours($age) => (int) -now()->diffInMinutes($age) . ' minutes',
				(int) now()->diffInDays($age) => (int) -now()->diffInHours($age) . ' hours',
				(int) now()->diffInWeeks($age) => (int) -now()->diffInDays($age) . ' days',
				(int) now()->diffInMonths($age) => (int) -now()->diffInWeeks($age) . ' weeks',
				(int) now()->diffInYears($age) => (int) -now()->diffInMonths($age) . ' months',
				default => now()->diffInYears($age) . ' years',
				// @codeCoverageIgnoreEnd
			};

			return $text . ' ago';
			// @codeCoverageIgnoreStart
		} catch (\Throwable) {
			return 'unknown';
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_data(bool $use_cache = false): string|null
	{
		try {
			if ($this->data === null || !$use_cache) {
				$raw_response = $use_cache ? (string) Cache::get($this->url) : '';
				if ($raw_response === '') {
					$raw_response = $this->fetchFromServer();
					Cache::put($this->url, $raw_response, now()->addDays($this->ttl_in_days));
					Cache::put($this->url . '_age', now(), now()->addDays($this->ttl_in_days));
				}
				$this->data = $raw_response;
			}

			return $this->data;
			// @codeCoverageIgnoreStart
		} catch (RequestFailedException $e) {
			Log::error(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());
		}
		$this->clear_cache();

		return null;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Runs the HTTP query and returns the result.
	 *
	 * @return string the plain JSON-encoded response
	 *
	 * @throws RequestFailedException
	 */
	private function fetchFromServer(): string
	{
		if (app()->runningUnitTests()) {
			throw new RequestFailedException('file_get_contents() failed: "testing" environment detected.');
		}
		// @codeCoverageIgnoreStart
		// we cannot code cov this part. APP_ENV is `testing` in testing mode.

		$opts = [
			'http' => [
				'method' => 'GET',
				'timeout' => 1,
				'header' => [
					'User-Agent: ' . ini_get('user_agent'),
				],
			],
		];
		$context = stream_context_create($opts);

		$raw = file_get_contents($this->url, false, $context);
		if ($raw === '') {
			throw new RequestFailedException('file_get_contents() failed');
		}

		return $raw;
		// @codeCoverageIgnoreEnd
	}
}
