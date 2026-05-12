<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminStatsRequest;
use App\Http\Resources\Models\AdminStatsResource;
use App\Services\AdminStatsService;
use Illuminate\Routing\Controller;

class AdminDashboardController extends Controller
{
	public function __construct(private AdminStatsService $service)
	{
	}

	public function stats(AdminStatsRequest $request): AdminStatsResource
	{
		$force = $request->boolean('force');
		$overview = $this->service->getOverview($force);

		return AdminStatsResource::fromOverview($overview);
	}
}
