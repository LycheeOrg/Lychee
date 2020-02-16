<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use Spatie\GuzzleRateLimiterMiddleware\Store;
use Illuminate\Support\Facades\Cache;
use Geocoder\Query\ReverseQuery;
use Illuminate\Cache\ArrayStore;

class Geodecoder
{
	/**
	 * Generate an id from current microtime.
	 *
	 * @return string generated ID
	 */
	public static function decodeLocation(float $latitude, float $longitude): string
	{
		if($latitude==0 || $longitude==0) return null;

		$stack = \GuzzleHttp\HandlerStack::create();
		$stack->push(\Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware::perSecond(1));

		$httpClient = new \GuzzleHttp\Client([
		    'handler' => $stack,
		    'timeout' => 30.0,
		]);

		$httpAdapter = new \Http\Adapter\Guzzle6\Client($httpClient);

		// Use filesystem adapter to cache data
		$filesystemAdapter = new \League\Flysystem\Adapter\Local(Storage::path(''));
		$filesystem = new \League\Flysystem\Filesystem($filesystemAdapter);
		$psr6Cache = new \Cache\Adapter\Filesystem\FilesystemCachePool($filesystem);

		$provider = new \Geocoder\Provider\Nominatim\Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', 'lychee laravel');
		$formatter = new \Geocoder\Formatter\StringFormatter();

		$cachedProvider = new \Geocoder\Provider\Cache\ProviderCache(
			$provider, // Provider to cache
			$psr6Cache // PSR-6 compatible cache
		);

		$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, Configs::get_value('lang'));
		$result_list = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));
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
