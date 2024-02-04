<?php

namespace App\Http\Middleware;

use App\Enum\StorageDiskType;
use App\Exceptions\RequestUnsupportedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;

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
		$storageAdapter = Storage::disk(StorageDiskType::LOCAL->value)->getAdapter();
		if (!($storageAdapter instanceof LocalFilesystemAdapter)) {
			throw new RequestUnsupportedException($request->url() . ' not implemented for non-local storage');
		}

		return $next($request);
	}
}
