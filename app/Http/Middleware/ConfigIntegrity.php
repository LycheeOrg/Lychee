<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Small update of the database to avoid sneaky people setting all the levels to 0 and thus skipping some checks...
 */
class ConfigIntegrity
{
	public const SE_FIELDS = [
		'default_user_quota',
		'timeline_photos_granularity',
		'timeline_albums_granularity',
		'timeline_left_border_enabled',
		'timeline_photo_date_format_year',
		'timeline_photo_date_format_month',
		'timeline_photo_date_format_day',
		'timeline_photo_date_format_hour',
		'timeline_album_date_format_year',
		'timeline_album_date_format_month',
		'timeline_album_date_format_day',
		'number_albums_per_row_mobile',
		'cache_ttl',
	];

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next)
	{
		try {
			DB::table('configs')->whereIn('key', self::SE_FIELDS)->update(['level' => 1]);
		} catch (\Exception $e) {
			// Do nothing: we are not installed yet, so we fail silently.
		}

		return $next($request);
	}
}