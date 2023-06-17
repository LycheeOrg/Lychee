<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default Log Channel
	|--------------------------------------------------------------------------
	|
	| This option defines the default log channel that gets used when writing
	| messages to the logs. The name specified in this option should match
	| one of the channels defined in the "channels" configuration array.
	|
	*/

	'default' => 'stack',

	/*
	|--------------------------------------------------------------------------
	| Deprecations Log Channel
	|--------------------------------------------------------------------------
	|
	| This option controls the log channel that should be used to log warnings
	| regarding deprecated PHP and library features. This allows you to get
	| your application ready for upcoming major versions of dependencies.
	|
	*/

	'deprecations' => [
		'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
		'trace' => false,
	],

	/*
	|--------------------------------------------------------------------------
	| Log Channels
	|--------------------------------------------------------------------------
	|
	| Here you may configure the log channels for your application. Out of
	| the box, Laravel uses the Monolog PHP logging library. This gives
	| you a variety of powerful log handlers / formatters to utilize.
	|
	| Available Drivers: "single", "daily", "slack", "syslog",
	|                    "errorlog", "monolog",
	|                    "custom", "stack"
	|
	*/

	'channels' => [
		'stack' => [
			'driver' => 'stack',
			'channels' => ['debug-daily', 'error', 'warning',  'notice'],
		],

		// Whatever debug log is needed
		// Mostly SQL requests
		'debug-daily' => [
			'path' => storage_path('logs/daily.log'),
			'driver' => 'daily',
			'level' => 'debug',
		],

		// Something went wrong
		'error' => [
			'path' => storage_path('logs/errors.log'),
			'driver' => 'single',
			'level' => 'error',
			'bubble' => false,
		],

		// Something may have gone wrong
		'warning' => [
			'path' => storage_path('logs/warning.log'),
			'driver' => 'single',
			'level' => 'warning',
			'bubble' => false,
		],

		// By the way...
		'notice' => [
			'path' => storage_path('logs/notice.log'),
			'driver' => 'daily',
			'level' => 'notice',
		],

		// Specific channel to check who is accessing Lychee
		'login' => [
			'path' => storage_path('logs/login.log'),
			'driver' => 'single',
			'level' => 'info',
		],
	],
];
