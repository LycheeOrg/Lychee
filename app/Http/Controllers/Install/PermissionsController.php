<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Actions\InstallUpdate\DefaultConfig;
use App\Actions\InstallUpdate\PermissionsChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PermissionsController extends Controller
{
	public function __construct(
		private PermissionsChecker $permissions,
		private DefaultConfig $config,
	) {
	}

	public function view(): View
	{
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
	}
}