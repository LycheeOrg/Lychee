<?php /** @noinspection PhpIncludeInspection */

use Installer\Middleware\InstallCheck;
use Installer\Routes;
use Installer\Config;
use Installer\View;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');  //On or Off

chdir('..');

include 'installer/Controller/Controller.php';
include 'installer/Templates/Template.php';

foreach (glob("installer/**/*.php") as $filename) {
	include_once $filename;
}
foreach (glob("installer/*.php") as $filename) {
	include_once $filename;
}

// Initialize
$config = new Config();
$routes = new Routes($config);
$view = new View();
$middleware = new InstallCheck();

// middleware
$check = $middleware->check();

// routes & dispatch
if ($check == false)
{
	$controller = $routes->dispatch();
	$view->apply($controller->view(), $controller->do());
}
else
{
	$view->apply('Migrate', $check);
}

