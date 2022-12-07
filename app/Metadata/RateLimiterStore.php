<?php

namespace App\Metadata;

use Illuminate\Support\Facades\Cache;
use Spatie\GuzzleRateLimiterMiddleware\Store;

/**
 * This class is used in {@link \App\Metadata\Geodecoder::getCode()}.
 */
class RateLimiterStore implements Store
{
	public function get(): array
	{
		return Cache::get('rate-limiter', []);
	}

	public function push(int $timestamp, int $limit): void
	{
		Cache::put('rate-limiter', array_merge($this->get(), [$timestamp]));
	}
}
