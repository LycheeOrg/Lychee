<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\Configs;
use App\Logs;
use Geocoder\Query\ReverseQuery;
use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;
use Storage;

class Geodecoder
{
	/**
	 * Get http provider with or without caching.
	 *
	 * @return mixed Geocoder Provider
	 */
	public static function getGeocoderProvider()
	{
		$stack = \GuzzleHttp\HandlerStack::create();
		$stack->push(\Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware::perSecond(1));

		$httpClient = new \GuzzleHttp\Client([
			'handler' => $stack,
			'timeout' => Configs::get_value('location_decoding_timeout'),
		]);

		$httpAdapter = new \Http\Adapter\Guzzle6\Client($httpClient);

		$cachedProvider = null;
		$caching_type = Configs::get_value('location_decoding_caching_type');

		$provider = new \Geocoder\Provider\Nominatim\Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', 'Lychee Laravel');

		if ($caching_type == 'Memory') {
			// Use Array Caching (in memory - only helps is involing command via console)
			$psr6Cache = new \Cache\Adapter\PHPArray\ArrayCachePool();

			return new \Geocoder\Provider\Cache\ProviderCache($provider, $psr6Cache);
		} elseif ($caching_type == 'Harddisk') {
			// Use filesystem adapter to cache data (writes files to /uploads/cache)

			$filesystemAdapter = new \League\Flysystem\Adapter\Local(Storage::path(''));
			$filesystem = new \League\Flysystem\Filesystem($filesystemAdapter);
			$psr6Cache = new \Cache\Adapter\Filesystem\FilesystemCachePool($filesystem);

			return new \Geocoder\Provider\Cache\ProviderCache(
						$provider, // Provider to cache
						$psr6Cache // PSR-6 compatible cache
					);
		}
		// No caching
		return $provider;
	}

	/**
	 * Decode GPS coordinates into location.
	 *
	 * @return string location
	 */
	public static function decodeLocation($latitude, $longitude)
	{
		// User does not want to decode location data
		if (Configs::get_value('location_decoding') == false) {
			return null;
		}
		if ($latitude == null || $longitude == null) {
			return null;
		}

		$cachedProvider = Geodecoder::getGeocoderProvider();

		return Geodecoder::decodeLocation_core($latitude, $longitude, $cachedProvider);
	}

	/**
	 * Decode GPS coordinates into location.
	 *
	 * @return string location
	 */
	public static function decodeLocation_core($latitude, $longitude, $cachedProvider)
	{
		$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, Configs::get_value('lang'));
		$result_list = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));

		// If no result has been returned -> return null
		if ($result_list->isEmpty()) {
			Logs::warning(__METHOD__, __LINE__, 'Location (' . $latitude . ', ' . $longitude . ') could not be decoded.');

			return null;
		}

		return $result_list->first()->getDisplayName();
	}
}

class RateLimiterStore implements Store
{
	public function get(): array
	{
		return Cache::get('rate-limiter', []);
	}

	public function push(int $timestamp)
	{
		Cache::put('rate-limiter', array_merge($this->get(), [$timestamp]));
	}
}
