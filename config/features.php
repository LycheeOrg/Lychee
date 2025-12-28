<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
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
	| Log 404 errors
	|--------------------------------------------------------------------------
	|
	| When enabled, all 404 errors are logged to the log file.
	| This can be useful to track broken links or attempted attacks.
	| True by default, so it can be set to false to avoid too large log files.
	*/
	'log_404_errors' => (bool) env('LOG_404_ERRORS', true),

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
	| Disable Basic Auth. This means that the only way to authenticate is via
	| the API token, Webauthn or Oauth.
	| This should only be toggled AFTER having set up the admin account and
	| bound the Oauth client.
	|--------------------------------------------------------------------------
	*/
	'disable-basic-auth' => (bool) env('DISABLE_BASIC_AUTH', false),

	/*
	|--------------------------------------------------------------------------
	| Disable WebAuthn. This means that the only way to authenticate is via
	| the API token, Basic Auth or Oauth.
	|--------------------------------------------------------------------------
	*/
	'disable-webauthn' => (bool) env('DISABLE_WEBAUTHN', false),

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

	/*
	 |--------------------------------------------------------------------------
	 | Disable import from server
	 |--------------------------------------------------------------------------
	 |
	 | This value determines whether the import from server feature is disabled.
	 | This is useful if you want to prevent users from importing files from the
	 | server and want to make sure the admin has no rights either.
	 |
	 | Effectively, this increases the security by limiting the attack surface.
	 | If the admin account is compromised, the attacker cannot use this feature to
	 | read files from the server such as .env, /etc/passwd et al.
	 */
	'disable-import-from-server' => (bool) env('DISABLE_IMPORT_FROM_SERVER', false),

	/*
	 |--------------------------------------------------------------------------
	 | Enable Webshop
	 |--------------------------------------------------------------------------
	 |
	 | This value determines whether the webshop feature is enabled.
	 | Disabling it hides all webshop related features
	 */
	'webshop' => (bool) env('WEBSHOP_ENABLED', true),

	/*
	 |--------------------------------------------------------------------------
	 | Populate Request object macros while testing
	 |--------------------------------------------------------------------------
	 |
	 | This is necessary for some unit tests that rely on the Request macros
	 | being present. In production, these macros are populated via middleware.
	 | However, in unit tests, the middleware may not be executed, leading to
	 | missing macros and test failures.
	 */
	'populate-request-macros' => (bool) env('POPULATE_REQUEST_MACROS', false),
];