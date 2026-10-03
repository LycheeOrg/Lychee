<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\InstallUpdate\ApplyMigration;
use App\Http\Requests\Maintenance\MigrateRequest;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Apply pending database migrations after the code has been updated.
 *
 * Similar to {@link \App\Http\Controllers\Install\MigrationController::view()},
 * which also generates a new application key during installation.
 */
class MigrateController extends Controller
{
	public function __construct(protected ApplyMigration $apply_migration)
	{
	}

	public function migrate(MigrateRequest $request): View
	{
		$output = $this->apply_migration->run();

		return view('update.results', ['code' => '200', 'message' => 'Migration results', 'output' => $output]);
	}
}
