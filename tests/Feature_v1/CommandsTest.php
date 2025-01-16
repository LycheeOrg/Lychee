<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Feature_v1;

use Tests\AbstractTestCase;

class CommandsTest extends AbstractTestCase
{
	/**
	 * Tests some console commands on a basic level.
	 *
	 * The command under tests are only invoked, but not tested thoroughly.
	 * In the long run, each of the commands should be tested by its own,
	 * dedicated test class with test thorough methods for every option and
	 * outcome of each command.
	 * Then this class and test method can be nuked.
	 *
	 * @return void
	 */
	public function testCommands(): void
	{
		$this->artisan('lychee:decode_GPS_locations')
			->expectsOutput('No photos or videos require processing.')
			->assertExitCode(0);

		$this->artisan('lychee:diagnostics')
			->assertExitCode(0);

		$this->artisan('lychee:exif_lens')
			->expectsOutput('No pictures requires EXIF updates.')
			->assertExitCode(-1);
	}
}
