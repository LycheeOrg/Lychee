<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Use VueJS Front-end
	|--------------------------------------------------------------------------
	|
	| This value determines whether livewire front-end is enabled as it is
	| currently under development.
	|
	*/
	'vuejs' => (bool) env('VUEJS_ENABLED', true),

	/*
	|--------------------------------------------------------------------------
	| Use VueJS Front-end
	|--------------------------------------------------------------------------
	|
	| This value determines whether livewire front-end is enabled as it is
	| currently under development.
	|
	*/
	'legacy_api' => (bool) env('LEGACY_API_ENABLED', !(bool) env('VUEJS_ENABLED', true)),

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
	| Use new code path when importing photos
	|--------------------------------------------------------------------------
	|
	| Use pipeline design pattern instead of hardcoded Strategies.
	*/
	'create-photo-via-pipes' => (bool) env('PHOTO_PIPES', false),

	/*
	|--------------------------------------------------------------------------
	| Use S3 buckets instead of local hosting.
	|--------------------------------------------------------------------------
	|
	| Put images on AWS instead of locally to save space.
	*/
	'use-s3' => (env('AWS_ACCESS_KEY_ID', '') !== '') && (bool) env('S3_ENABLED', false),
];