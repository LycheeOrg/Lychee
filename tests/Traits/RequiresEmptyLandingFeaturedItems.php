<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait RequiresEmptyLandingFeaturedItems
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyLandingFeaturedItems(): void
	{
		$this->assertDatabaseCount('landing_featured_items', 0);
	}

	protected function tearDownRequiresEmptyLandingFeaturedItems(): void
	{
		DB::table('landing_featured_items')->delete();
	}
}
