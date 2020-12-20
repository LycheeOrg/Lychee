<?php

namespace Tests\Feature;

use Tests\TestCase;

class Commands extends TestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testCommands()
	{
		$this->artisan('lychee:decode_GPS_locations')
			->expectsOutput('No photos or videos require processing.')
			->assertExitCode(0);

		$this->artisan('lychee:diagnostics')
			->assertExitCode(0);

		$this->artisan('lychee:exif_lens')
			->expectsOutput('No pictures requires EXIF updates.')
			->assertExitCode(0);

		$this->artisan('lychee:generate_thumbs')
			->expectsOutput('Not enough arguments (missing: "type").')
			->assertExitCode(1);

		$this->artisan('lychee:generate_thumbs', ['type' => 'smally'])
			->expectsOutput('Type smally is not one of small, small2x, medium, medium2')
			->assertExitCode(1);

		$this->artisan('lychee:generate_thumbs', ['type' => 'small'])
			->expectsOutput('Will attempt to generate up to 100 small (0x360) images with a timeout of 600 seconds...
No picture requires small.')->assertExitCode(0);

		$this->artisan('lychee:rebuild_albums_takestamps')->assertExitCode(0);

		$this->artisan('lychee:reset_admin')
			->expectsOutput('Admin username and password reset.')
			->assertExitCode(0);

		$this->artisan('lychee:logs')->assertExitCode(0);

		$this->artisan('lychee:logs', ['action' => 'clean'])
			->expectsOutput('Log table has been emptied.')->assertExitCode(0);

		$this->artisan('lychee:sync')
			->expectsOutput('Not enough arguments (missing: "dir").')
			->assertExitCode(1);

		$this->artisan('lychee:sync', ['dir' => 'public/small/'])
			->expectsOutput('Start syncing.
Problem: public/uploads/small/: Given path is reserved
Done syncing.')->assertExitCode(0);

		$this->artisan('lychee:takedate')->expectsOutput('No pictures requires takedate updates.')->assertExitCode(0);

		$this->artisan('lychee:video_data')->assertExitCode(0);
	}
}
