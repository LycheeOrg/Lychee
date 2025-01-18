<?php

ini_set('user_agent', 'Lychee/6 (https://lycheeorg.dev/)');

/*
 * Ensure that the umask does not deny any user or group permissions,
 * but honor the system-provided setting for world.
 *
 * The recommended setup is to run the web server within a security context
 * with its own owner and group, e.g. let's say `apache:www-data`.
 * An web admin user for CLI operations is supposed to be a member of the
 * group of the web server, e.g. `www-data` in the example above.
 * (Note, such an admin is not necessarily root).
 * Objects inside file-based caches (like the one below ./storage) must be
 * created group-writable.
 * Otherwise, the web admin cannot successfully clear the cache after an
 * upgrade (or run commands such as `./artisan optimize:clear` manually).
 */
\umask(0007 & umask());

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
	$_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
	Illuminate\Contracts\Http\Kernel::class,
	App\Http\Kernel::class
);

$app->singleton(
	Illuminate\Contracts\Console\Kernel::class,
	App\Console\Kernel::class
);

$app->singleton(
	Illuminate\Contracts\Debug\ExceptionHandler::class,
	App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
