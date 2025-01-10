<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Actions\InstallUpdate\Pipes\AllowMigrationCheck;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use App\Actions\InstallUpdate\Pipes\BranchCheck;
use App\Actions\InstallUpdate\Pipes\ComposerCall;
use App\Actions\InstallUpdate\Pipes\GitPull;
use Illuminate\Pipeline\Pipeline;
use function Safe\preg_replace;

class ApplyUpdate
{
	/**
	 * @var array<int,string> application of the updates
	 */
	private array $pipes = [
		BranchCheck::class,
		AllowMigrationCheck::class,
		GitPull::class,
		ArtisanMigrate::class,
		ComposerCall::class,
	];

	/**
	 * Applies the migration:
	 * 1. git pull
	 * 2. artisan migrate.
	 *
	 * @return array<int,string> the per-line console output
	 */
	public function run(): array
	{
		$output = [];

		$output = app(Pipeline::class)
			->send($output)
			->through($this->pipes)
			->thenReturn();

		return preg_replace('/\033[[][0-9]*;*[0-9]*;*[0-9]*m/', '', $output);
	}
}
