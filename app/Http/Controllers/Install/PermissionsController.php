<?php

namespace App\Http\Controllers\Install;

use App\ControllerFunctions\Install\DefaultConfig;
use App\ControllerFunctions\Install\PermissionsChecker;
use App\Http\Controllers\Controller;

final class PermissionsController extends Controller
{
	/**
	 * @var PermissionsChecker
	 */
	protected $permissions;
	/**
	 * @var DefaultConfig
	 */
	protected $config;

	/**
	 * @param PermissionsChecker $checker
	 * @param Config             $config
	 */
	public function __construct(PermissionsChecker $checker, DefaultConfig $config)
	{
		$this->permissions = $checker;
		$this->config = $config;
	}

	/**
	 * @return View
	 */
	public function view()
	{
		$perms = $this->permissions->check(
			$this->config->get_permissions()
		);

		return view('install.permissions', [
			'title' => 'Lychee-installer',
			'step' => 2,
			'permissions' => $perms['permissions'],
			'errors' => $perms['errors'],
		]);
	}
}