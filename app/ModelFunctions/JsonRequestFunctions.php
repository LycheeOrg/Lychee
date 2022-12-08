<?php

namespace App\ModelFunctions;

use App\Exceptions\Internal\JsonRequestFailedException;
use App\Models\Logs;
use Illuminate\Support\Facades\Cache;
use function Safe\file_get_contents;
use function Safe\ini_get;

class JsonRequestFunctions
{
	private string $url;
	private mixed $decodedJson;
	private int $ttl;

	/**
	 * JsonRequestFunctions constructor.
	 *
	 * @param string $url URL to request/cache
	 * @param int    $ttl Time-to-live of the cache in DAYS
	 */
	public function __construct(string $url, int $ttl = 1)
	{
		$this->url = $url;
		$this->decodedJson = null;
		$this->ttl = $ttl;
	}

	/**
	 * Remove elements from the cache.
	 */
	public function clear_cache(): void
	{
		$this->decodedJson = null;
		Cache::forget($this->url);
		Cache::forget($this->url . '_age');
	}

	/**
	 * Return the age of the last query in days/hours/minutes.
	 *
	 * @return string
	 */
	public function get_age_text(): string
	{
		$age = Cache::get($this->url . '_age');
		if (!$age instanceof \DateTimeInterface) {
			return 'unknown';
		}
		try {
			$text = match (0) {
				now()->diffInMinutes($age) => now()->diffInSeconds($age) . ' seconds',
				now()->diffInHours($age) => now()->diffInMinutes($age) . ' minutes',
				now()->diffInDays($age) => now()->diffInHours($age) . ' hours',
				now()->diffInWeeks($age) => now()->diffInDays($age) . ' days',
				now()->diffInMonths($age) => now()->diffInWeeks($age) . ' weeks',
				now()->diffInYears($age) => now()->diffInMonths($age) . ' months',
				default => now()->diffInYears($age) . ' years'
			};

			return $text . ' ago';
		} catch (\Throwable) {
			return 'unknown';
		}
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
				throw new JsonRequestFailedException('file_get_contents() failed');
			}

			return $raw;
		} catch (\Throwable $e) {
			throw new JsonRequestFailedException('Could not fetch ' . $this->url, $e);
		}
	}

	/**
	 * Returns the decoded JSON response.
	 *
	 * @param bool $useCache if true, the JSON response is not fetched but
	 *                       served from cache if available
	 *
	 * @return mixed the type of the response depends on the content of the
	 *               HTTP response and may be anything: a primitive type,
	 *               an array or an object
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
		} catch (JsonRequestFailedException $e) {
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
		} catch (\JsonException $e) {
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
		}
		$this->clear_cache();

		return null;
	}
}
