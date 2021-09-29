<?php

namespace App\Http\Middleware;

use App\Exceptions\RequestUnsupportedException;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
		$storageAdapter = Storage::disk()->getDriver()->getAdapter();
		if (!($storageAdapter instanceof Local)) {
			$msg = $request->url() . ' not implemented for non-local storage';
			Logs::error(__METHOD__, __LINE__, $msg);
			throw new RequestUnsupportedException($msg);
		}

		return $next($request);
	}
}