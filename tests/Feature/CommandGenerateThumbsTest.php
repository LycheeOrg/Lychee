<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommandGenerateThumbsTest extends TestCase
{
	public const COMMAND = 'lychee:generate_thumbs';

	public function testNoArguments(): void
	{
		$this->expectExceptionMessage('Not enough arguments (missing: "type").');
		$this->artisan(self::COMMAND)->run();
	}

	public function testInvalidSizeVariantArgument(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'smally'])
			->expectsOutput('Type smally is not one of small, small2x, medium, medium2x')
			->assertExitCode(1);
	}

	public function testNoSizeVariantsMissing(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'small'])
			->expectsOutput('No picture requires small.')
			->assertExitCode(0);
	}
}
