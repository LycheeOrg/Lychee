<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Assets\Features;
use App\Http\Requests\Admin\SecurityAdvisories\IndexSecurityAdvisoriesRequest;
use App\Http\Resources\Models\SecurityAdvisoryResource;
use App\Services\SecurityAdvisoriesService;
use Illuminate\Routing\Controller;

/**
 * Admin controller that exposes the list of security advisories matching
 * the currently installed Lychee version.
 *
 * Only administrators may call this endpoint. Non-admins receive 403.
 * When the vulnerability-check feature is disabled the endpoint returns an
 * empty array.
 */
class SecurityAdvisoriesController extends Controller
{
	public function __construct(
		private SecurityAdvisoriesService $service,
	) {
	}

	/**
	 * Return the list of advisories that affect the running Lychee version.
	 *
	 * @param IndexSecurityAdvisoriesRequest $_request
	 *
	 * @return SecurityAdvisoryResource[]
	 */
	public function index(IndexSecurityAdvisoriesRequest $_request): array
	{
		if (Features::inactive('vulnerability-check')) {
			return [];
		}

		return array_map(
			fn ($advisory) => SecurityAdvisoryResource::fromAdvisory($advisory),
			$this->service->getMatchingAdvisories(),
		);
	}
}

