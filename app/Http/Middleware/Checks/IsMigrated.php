<?php

namespace App\Http\Middleware\Checks;

use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Contracts\MiddlewareCheck;

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
