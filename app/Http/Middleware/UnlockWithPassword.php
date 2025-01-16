<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Actions\Album\Unlock;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class LoginRequired.
 *
 * This middleware is ensures that only logged in users can access Lychee.
 */
class UnlockWithPassword
{
	private AlbumFactory $albumFactory;
	private Unlock $unlock;

	public function __construct(AlbumFactory $albumFactory, Unlock $unlock)
	{
		$this->albumFactory = $albumFactory;
		$this->unlock = $unlock;
	}

	/**
	 * Handle an incoming request.
	 * If a password is provided, we try to unlock the album or fail silently.
	 *
	 * @param Request  $request the incoming request to serve
	 * @param \Closure $next    the next operation to be applied to the
	 *                          request
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		$album_id = $request->route('albumId');
		if ($album_id === null || !is_string($album_id)) {
			throw new LycheeLogicException('No albumId provided as url parameter.');
		}

		if (in_array($album_id, SmartAlbumType::values(), true)) {
			return $next($request);
		}

		if (!$request->filled('password')) {
			return $next($request);
		}

		if (!Configs::getValueAsBool('unlock_password_photos_with_url_param')) {
			Log::warning('password provided but unlock_password_photos_with_url_param is disabled.');

			return $next($request);
		}

		try {
			$album = $this->albumFactory->findBaseAlbumOrFail($album_id);
			$this->unlock->do($album, $request['password']);
		} catch (\Exception) {
			// fail silently
		}

		return $next($request);
	}
}
