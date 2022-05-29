<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommandsTest extends TestCase
{
	/**
	 * A basic feature test example.
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

		$this->artisan('lychee:reset_admin')
			->expectsOutput('Admin username and password reset.')
			->assertExitCode(0);

		$this->artisan('lychee:logs')
			->assertExitCode(0);

		$this->artisan('lychee:logs', ['action' => 'clean'])
			->expectsOutput('Log table has been emptied.')
			->assertExitCode(0);

		$this->artisan('lychee:video_data')
			->assertExitCode(0);
	}
}
