<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Checks;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Http\MiddlewareCheck;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class HasAdminUser implements MiddlewareCheck
{
	/**
	 * @throws InternalLycheeException
	 */
	public function assert(): bool
	{
		// This is necessary in the case that someone is using the web-updater.
		// In such case the tables already exist so the IsInstalled will return true.
		// However this middleware will throw an error because may_administrate does not exist yet.
		if (Schema::hasColumn('users', 'may_administrate')) {
			return User::query()->where('may_administrate', '=', true)->count() > 0;
		} else {
			// If the column does not exist yet but we are executing this script
			// it means that there exists already an admin user (with ID = 0).
			return true;
		}
	}
}