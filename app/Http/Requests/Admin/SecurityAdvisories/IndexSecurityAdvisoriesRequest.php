<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Admin\SecurityAdvisories;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * FormRequest for the Security Advisories index endpoint.
 *
 * Only administrators may retrieve advisory data.
 * Unauthenticated requests receive 401 (Laravel default for failed authorize).
 * Authenticated non-admin requests receive 403.
 */
class IndexSecurityAdvisoriesRequest extends AbstractEmptyRequest
{
	/**
	 * Only allow administrators to call this endpoint.
	 */
	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}
}
