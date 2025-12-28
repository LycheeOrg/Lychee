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
		'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'deprecations'),
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
			'channels' => [
				env('LOG_STDOUT', false) ? 'stdout' : null,
				'debug-daily',
				'error',
				'warning',
				'notice',
			],
		],

		// NEW: Stdout for container logs
		'stdout' => [
			'driver' => 'monolog',
			'level' => env('LOG_LEVEL', 'debug'),
			'handler' => \Monolog\Handler\StreamHandler::class,
			'formatter' => env('LOG_STDERR_FORMATTER'),
			'with' => [
				'stream' => 'php://stdout',
			],
			'processors' => [
				// Adds extra context
				\Monolog\Processor\WebProcessor::class,
				\Monolog\Processor\MemoryUsageProcessor::class,
			],
		],

		// Alternative: stderr for error-level logs
		'stderr' => [
			'driver' => 'monolog',
			'level' => 'error',
			'handler' => \Monolog\Handler\StreamHandler::class,
			'formatter' => env('LOG_STDERR_FORMATTER'),
			'with' => [
				'stream' => 'php://stderr',
			],
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

		'deprecations' => [
			'driver' => 'single',
			'path' => storage_path('logs/deprecations.log'),
			'level' => 'debug',
		],
	],
];
