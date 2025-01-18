<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata;

use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\LocationDecodingFailed;
use App\Models\Configs;
use Geocoder\Exception\Exception as GeocoderException;
use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Illuminate\Contracts\Container\BindingResolutionException;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

/**
 * @codeCoverageIgnore We know it works.
 */
class Geodecoder
{
	/**
	 * Get http provider with caching.
	 *
	 * @return ProviderCache Geocoder Provider
	 *
	 * @throws ExternalComponentFailedException
	 */
	public static function getGeocoderProvider(): ProviderCache
	{
		try {
			$stack = HandlerStack::create();
			$stack->push(RateLimiterMiddleware::perSecond(1));

			$httpClient = new \GuzzleHttp\Client([
				'handler' => $stack,
				'timeout' => Configs::getValueAsInt('location_decoding_timeout'),
			]);

			$httpAdapter = new \Http\Adapter\Guzzle7\Client($httpClient);

			$provider = new Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', config('app.name'));

			return new ProviderCache($provider, app('cache.store'));
		} catch (GeocoderException|GuzzleException|\RuntimeException|BindingResolutionException|\InvalidArgumentException $e) {
			throw new ExternalComponentFailedException('Could not create geocoder provider', $e);
		}
	}

	/**
	 * Decode GPS coordinates into location.
	 *
	 * @param ?float $latitude
	 * @param ?float $longitude
	 *
	 * @return ?string location
	 *
	 * @throws ExternalComponentFailedException
	 */
	public static function decodeLocation(?float $latitude, ?float $longitude): ?string
	{
		// User does not want to decode location data
		if (!Configs::getValueAsBool('location_decoding')) {
			return null;
		}
		if ($latitude === null || $longitude === null) {
			return null;
		}

		$cachedProvider = Geodecoder::getGeocoderProvider();

		return Geodecoder::decodeLocation_core($latitude, $longitude, $cachedProvider);
	}

	/**
	 * Wrapper to decode GPS coordinates into location.
	 *
	 * @param float         $latitude
	 * @param float         $longitude
	 * @param ProviderCache $cachedProvider
	 *
	 * @return ?string location
	 *
	 * @throws LocationDecodingFailed
	 */
	public static function decodeLocation_core(float $latitude, float $longitude, ProviderCache $cachedProvider): ?string
	{
		$lang = Configs::getValueAsString('lang');
		$geocoder = new StatefulGeocoder($cachedProvider, $lang);
		try {
			$result_list = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));

			// If no result has been returned -> return null
			if ($result_list->isEmpty()) {
				throw new LocationDecodingFailed('Location (' . $latitude . ', ' . $longitude . ') could not be decoded.');
			}

			/** @disregard P1013 */
			return $result_list->first()->getDisplayName();
			// @codeCoverageIgnoreStart
		} catch (GeocoderException $e) {
			throw new LocationDecodingFailed('Location (' . $latitude . ', ' . $longitude . ') could not be decoded.', $e);
		}
		// @codeCoverageIgnoreEnd
	}
}
