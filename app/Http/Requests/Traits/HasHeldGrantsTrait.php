<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Which grants the current user may hand out (Feature 118, FR-118-07).
 *
 * Validation runs before authorization in {@see \App\Http\Requests\BaseApiRequest}:
 * a guest is treated as holding every right so that authorization, not
 * validation, refuses them (401 rather than 422).
 */
trait HasHeldGrantsTrait
{
	protected function holdsUploadGrant(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user === null || $user->may_administrate || $user->may_upload;
	}

	protected function holdsEditGrants(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user === null || $user->may_administrate || $user->may_edit;
	}
}
