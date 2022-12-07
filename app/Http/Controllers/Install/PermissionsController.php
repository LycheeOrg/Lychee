<?php

namespace App\Http\Controllers\Install;

use App\Actions\Install\DefaultConfig;
use App\Actions\Install\PermissionsChecker;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PermissionsController extends Controller
{
	private PermissionsChecker $permissions;
	private DefaultConfig $config;

	/**
	 * @param PermissionsChecker $checker
	 * @param DefaultConfig      $config
	 */
	public function __construct(PermissionsChecker $checker, DefaultConfig $config)
	{
		$this->permissions = $checker;
		$this->config = $config;
	}

	/**
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(): View
	{
		try {
			$perms = $this->permissions->check(
				$this->config->get_permissions()
			);

			return view('install.permissions', [
				'title' => 'Lychee-installer',
				'step' => 2,
				'permissions' => $perms['permissions'],
				'errors' => $perms['errors'],
				'windows' => $this->permissions->is_win(),
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}
}
