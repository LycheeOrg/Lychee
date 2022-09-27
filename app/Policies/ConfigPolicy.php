<?php

namespace App\Policies;

use App\Models\User;

class ConfigPolicy extends BasePolicy
{
	public function canEdit(User $user): bool
	{
		// Note, the administrator is already handled in the `before()`
		// method and every one else is not allowed to create/delete users.
		return false;
	}
}
