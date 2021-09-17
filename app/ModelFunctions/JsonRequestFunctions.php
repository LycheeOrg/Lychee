<?php

namespace App\ModelFunctions;

use App\Models\Logs;
use Illuminate\Support\Facades\Cache;

class JsonRequestFunctions
{
	private string $url;
	private $json;
	private ?string $raw;
	private int $ttl;

	/**
	 * JsonRequestFunctions constructor.
	 *
	 * @param string $url URL to request/cache
	 * @param int    $ttl Time-to-live of the cache in DAYS
	 *
	 * @throws \JsonException thrown, if cached JSON result could not be decoded
	 */
	public function __construct(string $url, int $ttl = 1)
	{
		$this->url = $url;
		$this->json = json_decode(Cache::get($url), false, 512, JSON_THROW_ON_ERROR);
		$this->raw = null;
		$this->ttl = $ttl;
	}

	/**
	 * Remove elements from the cache.
	 */
	public function clear_cache()
	{
		Cache::forget($this->url);
		Cache::forget($this->url . '_age');
		$this->json = null;
		$this->raw = null;
	}

	/**
	 * return the age of the last query to url.
	 *
	 * @return mixed
	 */
	public function get_age()
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
	 * @throws \JsonException    thrown, if JSON response could not be decoded
	 * @throws \RuntimeException thrown, if response could not be read
	 */
	private function get()
	{
		$opts = [
			'http' => [
				'method' => 'GET',
				'timeout' => 1,
				'header' => [
					'User-Agent: PHP',
				],
			],
		];
		$context = stream_context_create($opts);

		$this->raw = file_get_contents($this->url, false, $context);
		if ($this->raw === false) {
			$this->raw = null;
			$this->json = null;
			$msg = 'Could not read "' . $this->url . '"';
			Logs::notice(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg);
		}

		try {
			Cache::put($this->url, $this->raw, now()->addDays($this->ttl));
			Cache::put($this->url . '_age', now(), now()->addDays($this->ttl));
		} catch (\InvalidArgumentException $e) {
			Logs::error(__METHOD__, __LINE__, 'Could not cache the result of the JSON query');
		}

		try {
			$this->json = json_decode($this->raw, false, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			$this->json = null;
			throw $e;
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
	 * @throws \RuntimeException thrown, if `$cashed === true` but the cache
	 *                           has been empty
	 * @throws \JsonException    thrown, if the JSON response could not be
	 *                           decoded
	 */
	public function get_json(bool $cached = false)
	{
		if ($cached) {
			if ($this->json === null) {
				throw new \RuntimeException('JSON is not in cache');
			}

			return $this->json;
		}

		return $this->get();
	}
}
