<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\InstallUpdate\CheckUpdateAvailability;
use App\Http\Requests\Admin\AdminUpdateStatusRequest;
use App\Http\Resources\Models\AdminUpdateStatusResource;
use Illuminate\Routing\Controller;

class AdminUpdateStatusController extends Controller
{
	/**
	 * Return update status information for the current installation.
	 */
	public function show(AdminUpdateStatusRequest $_request, CheckUpdateAvailability $check_update_availability): AdminUpdateStatusResource
	{
		return AdminUpdateStatusResource::fromAvailability($check_update_availability->get());
	}
}
