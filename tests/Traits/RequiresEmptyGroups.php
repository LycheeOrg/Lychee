<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait RequiresEmptyGroups
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyGroups(): void
	{
		$this->assertDatabaseCount('user_groups', 0);
	}

	protected function tearDownRequiresEmptyGroups(): void
	{
		DB::table('user_groups')->delete();
	}
}
