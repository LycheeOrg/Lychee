<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Checks;

use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Contracts\Http\MiddlewareCheck;

class IsMigrated implements MiddlewareCheck
{
	/**
	 * Returns true if the DB version is up to date.
	 *
	 * @return bool
	 */
	public function assert(): bool
	{
		return MigrationCheck::isUpToDate();
	}
}
