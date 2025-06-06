<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Use VueJS Front-end
	|--------------------------------------------------------------------------
	|
	| This value determines whether vuejs front-end is enabled as it is
	| currently under development.
	|
	*/
	'vuejs' => (bool) env('VUEJS_ENABLED', true),

	/*
	|--------------------------------------------------------------------------
	| Force HTTPS
	|--------------------------------------------------------------------------
	|
	| When running behind a proxy, it may be necessary for the urls to be
	| set as https for the reverse translation. You should set this if you
	| want to force the https scheme.
	*/
	'force_https' => (bool) env('APP_FORCE_HTTPS', false),

	/*
	|--------------------------------------------------------------------------
	| Enable v4 redirections
	|--------------------------------------------------------------------------
	|
	| When using new front-end old links to /#albumID/PhotoID are broken.
	| This provides here a way to avoid those.
	*/
	'legacy_v4_redirect' => (bool) env('LEGACY_V4_REDIRECT', false),

	'legacy_v3_db_prefix' => env('DB_OLD_LYCHEE_PREFIX', '') !== '',

	/*
	|--------------------------------------------------------------------------
	| Log Viewer
	|--------------------------------------------------------------------------
	|
	| Log Viewer can be disabled, so it's no longer accessible via browser.
	*/
	'log-viewer' => (bool) env('LOG_VIEWER_ENABLED', true),

	/*
	|--------------------------------------------------------------------------
	| Use S3 buckets instead of local hosting.
	|--------------------------------------------------------------------------
	|
	| Put images on AWS instead of locally to save space.
	*/
	'use-s3' => (env('AWS_ACCESS_KEY_ID', '') !== '') && (bool) env('S3_ENABLED', false),

	/*
	|--------------------------------------------------------------------------
	| Hide Lychee SE from config to allow for easier video
	|--------------------------------------------------------------------------
	*/
	'hide-lychee-SE' => (bool) env('HIDE_LYCHEE_SE_CONFIG', false),

	/*
	 |--------------------------------------------------------------------------
	 | Add latency on requests to simulate slower network. Time in ms.
	 | Disabled on production environment.
	 |--------------------------------------------------------------------------
	 */
	'latency' => env('APP_ENV', 'production') === 'production' ? 0 : (int) env('APP_DEBUG_LATENCY', 0),

	/*
	 |--------------------------------------------------------------------------
	 | Require the API requests to have the header "content-type: application/json"
	 | or "content-type: multipart/form-data" depending on the type.
	 |
	 | Note that this prevents the use of the API from the API documentation page.
	 |--------------------------------------------------------------------------
	 */
	'require-content-type' => (bool) env('REQUIRE_CONTENT_TYPE_ENABLED', true),

	/*
	 |--------------------------------------------------------------------------
	 | Require the API requests to have the header "content-type: application/json"
	 | or "content-type: multipart/form-data" depending on the type.
	 |
	 | Note that this prevents the use of the API from the API documentation page.
	 |--------------------------------------------------------------------------
	 */
	'user-groups' => (bool) env('USER_GROUPS_ENABLED', false),

	/*
	|--------------------------------------------------------------------------
	| Vite http proxy
	|--------------------------------------------------------------------------
	|
	| This value determines whether we accept connection from the vite http proxy.
	| This is not recommended as this will have some impact on the way sessions are handled,
	| notably with gracefully reloading the page.
	*/
	'vite-http-proxy' => env('VITE_HTTP_PROXY_TARGET', '') !== '' ||
		((bool) env('VITE_HTTP_PROXY_ENABLED', false)) !== false ||
		((bool) env('VITE_LOCAL_DEV', false)) !== false,
];