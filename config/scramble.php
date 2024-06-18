<?php

declare(strict_types=1);

return [
	/*
	 * Your API path. By default, all routes starting with this path will be added to the docs.
	 * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
	 */
	'api_path' => 'api',

	/*
	 * Your API domain. By default, app domain is used. This is also a part of the default API routes
	 * matcher, so when implementing your own, make sure you use this config if needed.
	 */
	'api_domain' => null,

	'info' => [
		/*
		 * API version.
		 */
		'version' => env('API_VERSION', '0.0.1'),

		/*
		 * Description rendered on the home page of the API documentation (`/docs/api`).
		 */
		'description' => '',
	],

	/*
	 * The list of servers of the API. By default (when `null`), server URL will be created from
	 * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
	 * will need to specify the local server URL manually (if needed).
	 *
	 * Example of non-default config (final URLs are generated using Laravel `url` helper):
	 *
	 * ```php
	 * 'servers' => [
	 *     'Live' => 'api',
	 *     'Prod' => 'https://scramble.dedoc.co/api',
	 * ],
	 * ```
	 */
	'servers' => null,

	'middleware' => [
		// Only available for admin
		'web-admin',
	],

	'extensions' => [],
];
