<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\InstallUpdate\CheckUpdate;
use App\Assets\Features;
use App\Http\Requests\Admin\AdminUpdateStatusRequest;
use App\Http\Resources\Models\AdminUpdateStatusResource;
use Illuminate\Routing\Controller;

class AdminUpdateStatusController extends Controller
{
	public function __construct(private CheckUpdate $check_update)
	{
	}

	/**
	 * Return update status information for the current installation.
	 */
	public function show(AdminUpdateStatusRequest $_request): AdminUpdateStatusResource
	{
		if (Features::inactive('update-check')) {
			return AdminUpdateStatusResource::disabled();
		}

		$update_status = $this->check_update->getCode();

		return AdminUpdateStatusResource::fromUpdateStatus(
			$update_status,
			$this->check_update->getCurrentVersion(),
			$this->check_update->getLatestVersion(),
		);
	}
}
