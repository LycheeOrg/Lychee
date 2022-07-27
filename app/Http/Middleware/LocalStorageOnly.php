<?php

namespace App\Http\Middleware;

use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\RequestUnsupportedException;
use Illuminate\Http\Request;
use League\Flysystem\Adapter\Local;

class LocalStorageOnly
{
	/**
	 * Handles an incoming request.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 *
	 * @throws RequestUnsupportedException
	 */
	public function handle(Request $request, \Closure $next)
	{
		$storageAdapter = SizeVariantNamingStrategy::getImageDisk()->getDriver()->getAdapter();
		if (!($storageAdapter instanceof Local)) {
			throw new RequestUnsupportedException($request->url() . ' not implemented for non-local storage');
		}

		return $next($request);
	}
}
