<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
 |--------------------------------------------------------------------------
 | Include a very simple error display
 |--------------------------------------------------------------------------
 |
 | In the case where Composer and the nice error handler from Laravel is
 | not provided we still may need to display erros. This gives us access
 | to a simple pretty error page via the function displaySimpleError()
 | instead of a plain white blank one with error XXX in the top left
 | corner.
 */
require_once __DIR__ . '/../bootstrap/PanicAttack.php';

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
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
	require __DIR__ . '/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
	$request = Request::capture()
))->send();

$kernel->terminate($request, $response);
