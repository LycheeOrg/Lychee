<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserGroupMembershipChanged
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param int $user_id The ID of the user whose group membership changed
	 */
	public function __construct(public int $user_id)
	{
	}
}
