<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Moderation;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Authorization guard for the moderation list endpoint.
 *
 * Only administrators may access the moderation queue.
 */
class ListModerationRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}
}
