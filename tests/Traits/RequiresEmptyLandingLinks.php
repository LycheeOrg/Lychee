<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait RequiresEmptyLandingLinks
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyLandingLinks(): void
	{
		// Migrations seed built-in, non-deletable "Gallery"/"Contact" rows; clear
		// them too so tests using this trait keep operating against a genuinely
		// empty table.
		DB::table('landing_links')->delete();
		$this->assertDatabaseCount('landing_links', 0);
	}

	protected function tearDownRequiresEmptyLandingLinks(): void
	{
		DB::table('landing_links')->delete();
	}
}
