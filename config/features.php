<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Use Livewire Front-end
	|--------------------------------------------------------------------------
	|
	| This value determines whether livewire front-end is enabled as it is
	| currently under development.
	|
	*/
	'livewire' => (bool) env('LIVEWIRE_ENABLED', true),

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
	| Log Viewer can be disabled, so it's no longer accessible via browser.
	|
	*/
	'log-viewer' => (bool) env('LOG_VIEWER_ENABLED', true),
];