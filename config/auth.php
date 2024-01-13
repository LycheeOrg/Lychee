<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Authentication Defaults
	|--------------------------------------------------------------------------
	|
	| This option controls the default authentication "guard" and password
	| reset options for your application. You may change these defaults
	| as required, but they're a perfect start for most applications.
	|
	*/

	'defaults' => [
		'guard' => 'lychee',
		'passwords' => 'users',
	],

	/*
	|--------------------------------------------------------------------------
	| Authentication Guards
	|--------------------------------------------------------------------------
	|
	| Next, you may define every authentication guard for your application.
	| Of course, a great default configuration has been defined for you
	| here which uses session storage and the Eloquent user provider.
	|
	| All authentication drivers have a user provider. This defines how the
	| users are actually retrieved out of your database or other storage
	| mechanisms used by this application to persist your user's data.
	|
	| Supported: "session", "token"
	|
	| The custom identifier "session-or-token" is registered in
	| App\Providers\AuthServiceProvider and resolves to
	| App\Services\Auth\SessionOrTokenGuard.
	*/

	'guards' => [
		'lychee' => [
			'driver' => env('ENABLE_BEARER_TOKEN_AUTH', env('ENABLE_TOKEN_AUTH', true)) ? 'session-or-token' : 'session', // @phpstan-ignore-line
			'provider' => 'users',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| User Providers
	|--------------------------------------------------------------------------
	|
	| All authentication drivers have a user provider. This defines how the
	| users are actually retrieved out of your database or other storage
	| mechanisms used by this application to persist your user's data.
	|
	| If you have multiple user tables or models you may configure multiple
	| sources which represent each model / table. These sources may then
	| be assigned to any extra authentication guards you have defined.
	|
	| Supported: "database", "eloquent"
	|
	*/

	'providers' => [
		'users' => [
			'driver' => 'eloquent-webauthn',
			'model' => App\Models\User::class,
			'password_fallback' => true,
		],

		// 'users' => [
		// 	'driver' => 'database',
		// 	'table' => 'users',
		// ],
	],

	/*
	|--------------------------------------------------------------------------
	| Resetting Passwords
	|--------------------------------------------------------------------------
	|
	| You may specify multiple password reset configurations if you have more
	| than one user table or model in the application and you want to have
	| separate password reset settings based on the specific user types.
	|
	| The expire time is the number of minutes that the reset token should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	|
	*/

	'passwords' => [
		'users' => [
			'provider' => 'users',
			'table' => 'password_resets',
			'expire' => 60,
			'throttle' => 60,
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Password Confirmation Timeout
	|--------------------------------------------------------------------------
	|
	| Here you may define the amount of seconds before a password confirmation
	| times out and the user is prompted to re-enter their password via the
	| confirmation screen. By default, the timeout lasts for three hours.
	|
	*/

	'password_timeout' => 10800,

	/*
	|--------------------------------------------------------------------------
	| Hard fail on bearer token
	|--------------------------------------------------------------------------
	|
	| When a bearer token is found, we fail hard by throwing an exception when the
	| associated authenticable (user) is not found.
	|
	| This is only used if ENABLE_BEARER_TOKEN_AUTH = true
	*/

	'token_guard' => [
		// Hard fail if bearer token is provided but no authenticable user is found
		'fail_bearer_authenticable_not_found' => (bool) env('FAIL_NO_AUTHENTICABLE_BEARER_TOKEN', true),

		// Log if token is provided but no bearer prefix.
		'log_warn_no_scheme_bearer' => (bool) env('LOG_WARN_NO_BEARER_TOKEN', true),
	],
];
