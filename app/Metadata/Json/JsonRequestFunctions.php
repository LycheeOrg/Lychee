<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Json;

use App\Contracts\JsonRequest;
use App\Exceptions\Internal\JsonRequestFailedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use function Safe\file_get_contents;
use function Safe\ini_get;

class JsonRequestFunctions implements JsonRequest
{
	private string $url;
	private mixed $decodedJson;
	private int $ttl;

	/**
	 * {@inheritDoc}
	 */
	public function init(string $url, int $ttl): void
	{
		$this->url = $url;
		$this->decodedJson = null;
		$this->ttl = $ttl;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function clear_cache(): void
	{
		$this->decodedJson = null;
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
	public function get_json(bool $useCache = false): mixed
	{
		try {
			if ($this->decodedJson === null || !$useCache) {
				$rawResponse = $useCache ? (string) Cache::get($this->url) : '';
				if ($rawResponse === '') {
					$rawResponse = $this->fetchFromServer();
					Cache::put($this->url, $rawResponse, now()->addDays($this->ttl));
					Cache::put($this->url . '_age', now(), now()->addDays($this->ttl));
				}

				$this->decodedJson = json_decode($rawResponse, false, 512, JSON_THROW_ON_ERROR);
			}

			return $this->decodedJson;
			// @codeCoverageIgnoreStart
		} catch (JsonRequestFailedException $e) {
			Log::error(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());
		} catch (\JsonException $e) {
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
	 * @throws JsonRequestFailedException
	 */
	private function fetchFromServer(): string
	{
		try {
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
				// @codeCoverageIgnoreStart
				throw new JsonRequestFailedException('file_get_contents() failed');
				// @codeCoverageIgnoreEnd
			}

			return $raw;
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new JsonRequestFailedException('Could not fetch ' . $this->url, $e);
		}
		// @codeCoverageIgnoreEnd
	}
}
