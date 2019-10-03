<?php


namespace Installer\Controller;


use Installer\Config;
use Installer\Helpers\PermissionsChecker;

final class PermissionsController implements Controller
{
	/**
	 * @var PermissionsChecker
	 */
	protected $permissions;
	/**
	 * @var Config
	 */
	protected $config;



	/**
	 * @param  PermissionsChecker  $checker
	 * @param  Config  $config
	 */
	public function __construct(PermissionsChecker $checker, Config $config)
	{
		$this->permissions = $checker;
		$this->config = $config;
	}



	/**
	 * @return array
	 */
	public function do(){
		$permissions = $this->permissions->check(
			$this->config->get_permissions()
		);

		return $permissions;
	}



	/**
	 * @return string
	 */
	public function view(){
		return 'Permissions';
	}
}