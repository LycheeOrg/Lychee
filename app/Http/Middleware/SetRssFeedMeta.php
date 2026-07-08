<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;

/**
 * Injects the admin-configured RSS feed title/description into the
 * spatie/laravel-feed config at request time.
 *
 * The spatie FeedController reads the feed config before it invokes our
 * RSSController, so the override cannot happen inside the controller and must
 * run here, before the feed is built. A blank setting is ignored so that the
 * static defaults in config/feed.php remain in effect.
 */
class SetRssFeedMeta
{
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
		$configs = $request->configs();

		if ($configs->hasKey('rss_title')) {
			$title = $configs->getValueAsString('rss_title');
			if ($title !== '') {
				config(['feed.feeds.main.title' => $title]);
			}
		}

		if ($configs->hasKey('rss_description')) {
			$description = $configs->getValueAsString('rss_description');
			if ($description !== '') {
				config(['feed.feeds.main.description' => $description]);
			}
		}

		return $next($request);
	}
}
