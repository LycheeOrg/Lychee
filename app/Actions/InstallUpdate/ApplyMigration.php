<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use Illuminate\Pipeline\Pipeline;
use function Safe\preg_replace;

/**
 * Apply pending database migrations.
 * Updating the code itself (git, composer, docker, release archive) happens outside of Lychee.
 */
class ApplyMigration
{
	/**
	 * @return array<int,string> the per-line console output
	 */
	public function run(): array
	{
		$output = app(Pipeline::class)
			->send([])
			->through([ArtisanMigrate::class])
			->thenReturn();

		return preg_replace('/\033[[][0-9]*;*[0-9]*;*[0-9]*m/', '', $output);
	}
}
