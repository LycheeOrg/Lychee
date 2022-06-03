<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommandTakeDateTest extends TestCase
{
	public const COMMAND = 'lychee:takedate';

	public function testNoUpdateRequired(): void
	{
		$this->artisan(self::COMMAND)
			->expectsOutput('No pictures require takedate updates.')
			->assertExitCode(-1);
	}
}
