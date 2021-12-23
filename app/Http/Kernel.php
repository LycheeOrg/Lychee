<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
	/**
	 * The application's global HTTP middleware stack.
	 *
	 * These middleware are run during every request to your application.
	 *
	 * @var array
	 */
	protected $middleware = [
		\App\Http\Middleware\FixStatusCode::class,
		\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
		\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
		\App\Http\Middleware\TrimStrings::class,
		\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
		\Bepsvpt\SecureHeaders\SecureHeadersMiddleware::class,
	];

	/**
	 * The application's route middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [
		'web' => [
			\App\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],

		'web-admin' => [
			\App\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			'admin',
		],

		'install' => [
			\App\Http\Middleware\InstalledCheck::class,
		],

		'api' => [
			'throttle:api',
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],
	];

	/**
	 * The application's route middleware.
	 *
	 * These middlewares may be assigned to groups or used individually.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
		'can' => \Illuminate\Auth\Middleware\Authorize::class,
		'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
		'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
		'login' => \App\Http\Middleware\LoginCheck::class,
		'admin' => \App\Http\Middleware\AdminCheck::class,
		'upload' => \App\Http\Middleware\UploadCheck::class,
		'installed' => \App\Http\Middleware\DBExists::class,
		'migrated' => \App\Http\Middleware\MigrationCheck::class,
		'local_storage' => \App\Http\Middleware\LocalStorageOnly::class,
	];
}
