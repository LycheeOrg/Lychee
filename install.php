<?php /** @noinspection PhpIncludeInspection */

use Installer\Routes;
use Installer\Config;
use Installer\View;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');  //On or Off

include 'installer/Controller/Controller.php';
include 'installer/Templates/Template.php';

foreach (glob("installer/**/*.php") as $filename) {
	include_once $filename;
}
foreach (glob("installer/*.php") as $filename) {
	include_once $filename;
}

$config = new Config();
$routes = new Routes($config);
$view = new View();
$controller = $routes->dispatch();
$view->apply($controller->view(), $controller->do());


