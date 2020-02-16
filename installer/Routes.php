<?php


namespace Installer;

use Installer\Controller\EnvController;
use Installer\Controller\MigrationController;
use Installer\Controller\PermissionsController;
use Installer\Controller\RequirementsController;
use Installer\Controller\WelcomeController;
use Installer\Helpers\PermissionsChecker;
use Installer\Helpers\RequirementsChecker;

class Routes
{

	protected $config;



	public function __construct(Config $config)
	{
		$this->config = $config;
	}



	public function dispatch()
	{

		if (!isset($_GET['step'])) {
			$controller = new WelcomeController();
			return $controller;
		}

		if ($_GET['step'] == 'req') {
			$requirement = new RequirementsChecker();
			$controller = new RequirementsController($requirement,
				$this->config);

			return $controller;
		}

		if ($_GET['step'] == 'perm') {
			$requirement = new PermissionsChecker();
			$controller = new PermissionsController($requirement,
				$this->config);

			return $controller;
		}

		if ($_GET['step'] == 'migrate') {
			$controller = new MigrationController();

			return $controller;
		}

		if ($_GET['step'] == 'env') {
			$controller = new EnvController();

			return $controller;
		}

		$controller = new WelcomeController();
		return $controller;

	}
}