<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
	/**
	 * Creates the application.
	 *
	 * @return Application
	 */
	public function createApplication(): Application
	{
		$app = require __DIR__ . '/../bootstrap/app.php';

		$app->make(Kernel::class)->bootstrap();

		return $app;
	}
}
