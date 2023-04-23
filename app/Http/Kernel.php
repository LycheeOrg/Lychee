<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
	/**
	 * The application's global HTTP middleware stack.
	 *
	 * These middlewares are run during every request to your application.
	 *
	 * @var array<int,string>
	 */
	protected $middleware = [
		\App\Http\Middleware\FixStatusCode::class,
		\Illuminate\Http\Middleware\TrustProxies::class,
		\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
		\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
		\App\Http\Middleware\TrimStrings::class,
		\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
		\Bepsvpt\SecureHeaders\SecureHeadersMiddleware::class,
	];

	/**
	 * The application's route middleware groups.
	 *
	 * @var array<string,array<int,string>>
	 */
	protected $middlewareGroups = [
		'web' => [
			'installation:complete',
			'admin_user:set',
			'accept_content_type:html',
			\Illuminate\Cookie\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],

		'web-admin' => [
			'accept_content_type:html',
			\Illuminate\Cookie\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],

		'web-install' => [
			'accept_content_type:html',
			'installation:incomplete',
		],

		'api' => [
			'installation:complete',
			'admin_user:set',
			'accept_content_type:json',
			'content_type:json',
			\Illuminate\Cookie\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],
	];

	/**
	 * The application's route middleware.
	 *
	 * These middlewares may be assigned to groups or used individually.
	 *
	 * @var array<string, string>
	 */
	protected $middlewareAliases = [
		'installation' => \App\Http\Middleware\InstallationStatus::class,
		'admin_user' => \App\Http\Middleware\AdminUserStatus::class,
		'migration' => \App\Http\Middleware\MigrationStatus::class,
		'local_storage' => \App\Http\Middleware\LocalStorageOnly::class,
		'content_type' => \App\Http\Middleware\ContentType::class,
		'accept_content_type' => \App\Http\Middleware\AcceptContentType::class,
		'redirect-legacy-id' => \App\Http\Middleware\RedirectLegacyPhotoID::class,
		'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
	];
}
