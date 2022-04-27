<?php

namespace App\ModelFunctions;

use App\Exceptions\Internal\JsonRequestFailedException;
use Illuminate\Support\Facades\Cache;

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
		if (!$age) {
			$last = 'unknown';
			$end = '';
		} else {
			try {
				$last = now()->diffInDays($age);
				$end = $last > 0 ? ' days' : '';
				$last = ($last == 0 && $end = ' hours')
					? now()->diffInHours($age) : $last;
				$last = ($last == 0 && $end = ' minutes')
					? now()->diffInMinutes($age) : $last;
				$last = ($last == 0 && $end = ' seconds')
					? now()->diffInSeconds($age) : $last;
				$end = $end . ' ago';
			} catch (\Throwable) {
				$last = 'unknown';
				$end = '';
			}
		}

		return $last . $end;
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
			if (!is_string($raw) || empty($raw)) {
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
	 *
	 * @throws JsonRequestFailedException
	 * @throws \JsonException
	 */
	public function get_json(bool $useCache = false): mixed
	{
		try {
			if ($this->decodedJson === null || !$useCache) {
				$rawResponse = $useCache ? Cache::get($this->url) : null;
				if (empty($rawResponse)) {
					$rawResponse = $this->fetchFromServer();
					Cache::put($this->url, $rawResponse, now()->addDays($this->ttl));
					Cache::put($this->url . '_age', now(), now()->addDays($this->ttl));
				}

				$this->decodedJson = json_decode($rawResponse, false, 512, JSON_THROW_ON_ERROR);
			}

			return $this->decodedJson;
		} catch (\JsonException $e) {
			$this->clear_cache();
			throw $e;
		}
	}
}
