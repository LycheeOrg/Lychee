<?php

namespace Tests\Feature;

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
		$cmd = $this->artisan('lychee:decode_GPS_locations');
		$this->assertIsNotInt($cmd);
		$cmd->expectsOutput('No photos or videos require processing.')
			->assertExitCode(0);

		$cmd = $this->artisan('lychee:diagnostics');
		$this->assertIsNotInt($cmd);
		$cmd->assertExitCode(0);

		$cmd = $this->artisan('lychee:exif_lens');
		$this->assertIsNotInt($cmd);
		$cmd->expectsOutput('No pictures requires EXIF updates.')
			->assertExitCode(-1);

		$cmd = $this->artisan('lychee:reset_admin');
		$this->assertIsNotInt($cmd);
		$cmd->expectsOutput('Admin username and password reset.')
			->assertExitCode(0);

		$cmd = $this->artisan('lychee:logs');
		$this->assertIsNotInt($cmd);
		$cmd->assertExitCode(0);

		$cmd = $this->artisan('lychee:logs', ['action' => 'clean']);
		$this->assertIsNotInt($cmd);
		$cmd->expectsOutput('Log table has been emptied.')
			->assertExitCode(0);
	}
}
