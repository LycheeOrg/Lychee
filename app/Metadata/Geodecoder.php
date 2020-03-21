<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Metadata;

use App\Configs;
use App\Logs;
use Cache;
use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\HandlerStack;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class Geodecoder
{
	/**
	 * Get http provider with or without caching.
	 *
	 * @return mixed Geocoder Provider
	 */
	public static function getGeocoderProvider()
	{
		$stack = HandlerStack::create();
		$stack->push(RateLimiterMiddleware::perSecond(1));

		$httpClient = new \GuzzleHttp\Client([
			'handler' => $stack,
			'timeout' => Configs::get_value('location_decoding_timeout'),
		]);

		$httpAdapter = new \Http\Adapter\Guzzle6\Client($httpClient);

		$cachedProvider = null;
		// $caching_type = Configs::get_value('location_decoding_caching_type');

		$provider = new Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', config('app.name'));

		return new ProviderCache($provider, app('cache.store'));
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
		$geocoder = new StatefulGeocoder($cachedProvider, Configs::get_value('lang'));
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
