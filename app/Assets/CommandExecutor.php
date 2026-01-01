<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Assets;

use function Safe\chdir;
use function Safe\exec;
use function Safe\putenv;

/**
 * This class exists for the sole purpose of allowing to tests the update mechanism and surrounding logic.
 * This will avoid calling directly exec and changing the state of the running test system.
 *
 * @codeCoverageIgnore
 */
class CommandExecutor
{
	public function exec(string $command, ?array &$output = null, ?int &$code = null): void
	{
		exec($command, $output, $code);
	}

	public function chdir(string $directory): void
	{
		chdir($directory);
	}

	public function putenv(string $setting): void
	{
		putenv($setting);
	}
}