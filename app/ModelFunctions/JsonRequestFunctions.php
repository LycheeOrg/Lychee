<?php

namespace App\ModelFunctions;

use App\Exceptions\Internal\JsonRequestFailedException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class JsonRequestFunctions
{
	private string $url;
	private mixed $json;
	private int $ttl;

	/**
	 * JsonRequestFunctions constructor.
	 *
	 * @param string $url URL to request/cache
	 * @param int    $ttl Time-to-live of the cache in DAYS
	 *
	 * @throws JsonRequestFailedException
	 */
	public function __construct(string $url, int $ttl = 1)
	{
<<<<<<< HEAD
		$this->url = $url;
		$this->json = json_decode(Cache::get($url) ?? '');
		$this->ttl = $ttl;
	}

	/**
	 * Cache the result of the request.
	 */
	private function cache()
	{
=======
>>>>>>> consistent_json_api
		try {
			$this->url = $url;
			$cached = Cache::get($url);
			$this->json = is_string($cached) && !empty($cached) ?
				json_decode($cached, false, 512, JSON_THROW_ON_ERROR) :
				null;
			$this->ttl = $ttl;
		} catch (\JsonException $e) {
			throw new JsonRequestFailedException('Could not decode JSON', $e);
		}
	}

	/**
	 * Remove elements from the cache.
	 */
	public function clear_cache(): void
	{
		$this->json = null;
		Cache::forget($this->url);
		Cache::forget($this->url . '_age');
	}

	/**
	 * return the age of the last query to url.
	 *
	 * @return Carbon
	 */
	public function get_age(): Carbon
	{
		return Cache::get($this->url . '_age');
	}

	/**
	 * Return the age of the last query in days/hours/minutes.
	 *
	 * @return string
	 */
	public function get_age_text(): string
	{
		$age = $this->get_age();
		if (!$age) {
			$last = 'unknown';
			$end = '';
		} else {
			$last = now()->diffInDays($age);
			$end = $last > 0 ? ' days' : '';
			$last = ($last == 0 && $end = ' hours')
				? now()->diffInHours($age) : $last;
			$last = ($last == 0 && $end = ' minutes')
				? now()->diffInMinutes($age) : $last;
			$last = ($last == 0 && $end = ' seconds')
				? now()->diffInSeconds($age) : $last;
			$end = $end . ' ago';
		}

		return $last . $end;
	}

	/**
	 * Runs the HTTP query and caches the result.
	 *
	 * @return mixed the type of the response depends on the content of the
	 *               HTTP response and may be anything: a primitive type,
	 *               an array or an object
	 *
	 * @throws JsonRequestFailedException
	 */
	private function get(): mixed
	{
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
			$this->clear_cache();
			throw new JsonRequestFailedException('Could not read "' . $this->url . '"');
		}

		try {
			$this->json = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
			Cache::put($this->url, $raw, now()->addDays($this->ttl));
			Cache::put($this->url . '_age', now(), now()->addDays($this->ttl));
		} catch (\JsonException $e) {
			$this->clear_cache();
			throw new JsonRequestFailedException('Could not read "' . $this->url . '"', $e);
		}

		return $this->json;
	}

	/**
	 * Returns the decoded JSON response.
	 *
	 * @param bool $cached if true, the JSON response is not fetched but
	 *                     served from cache
	 *
	 * @return mixed the type of the response depends on the content of the
	 *               HTTP response and may be anything: a primitive type,
	 *               an array or an object
	 *
	 * @throws JsonRequestFailedException
	 */
	public function get_json(bool $cached = false): mixed
	{
		if ($cached && $this->json !== null) {
			return $this->json;
		}

		return $this->get();
	}
}
