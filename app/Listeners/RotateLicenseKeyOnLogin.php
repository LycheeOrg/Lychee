<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Jobs\RotateLicenseKeyJob;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class RotateLicenseKeyOnLogin
{
	public function handle(Login $event): void
	{
		$user = $event->user;

		if (!$user instanceof User || $user->may_administrate !== true) {
			return;
		}

		RotateLicenseKeyJob::dispatch();
	}
}
