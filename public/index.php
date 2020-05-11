<?php

/**
 * Laravel - A PHP Framework For Web Artisans.
 *
 * @author   Taylor Otwell <taylor@laravel.com>
 */
define('LARAVEL_START', microtime(true));

/*
 |--------------------------------------------------------------------------
 | Include a very simple error display
 |--------------------------------------------------------------------------
 |
 | In the case where Composer and the nice error handler from Laravel is
 | not provided we still may need to display erros. This gives us access
 | to a simple pretty error page via the function
 | displaySimpleError($tite, $code, $message) instead of a plain white
 | blank one with error XXX in the top left corner.
 */
require_once __DIR__ . '/../bootstrap/simple-errors.php';

/*
 |--------------------------------------------------------------------------
 | Initialize before loading composer
 |--------------------------------------------------------------------------
 |
 | Include a small error handler in the case of composer is not found.
 */
require __DIR__ . '/../bootstrap/initialize.php';

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

$composer_not_found = true;
require __DIR__ . '/../vendor/autoload.php';

// we disable the onComposerNotFoundDie() handler.
$composer_not_found = false;

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
	$request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
