<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\InstallUpdate\Pipes;

use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use Illuminate\Support\Facades\Artisan;
use Tests\AbstractTestCase;

class ArtisanMigrateTest extends AbstractTestCase
{
	private ArtisanMigrate $artisanMigrate;

	protected function setUp(): void
	{
		parent::setUp();

		$this->artisanMigrate = new ArtisanMigrate();
	}

	public function testHandleCallsArtisanMigrateWithForceFlag(): void
	{
		// Arrange
		$output = [];
		$nextCalled = false;

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn('Migration completed successfully');

		$next = function (array $output) use (&$nextCalled): array {
			$nextCalled = true;

			return $output;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertCount(1, $result);
		$this->assertEquals('Migration completed successfully', $result[0]);
	}

	public function testHandleAppendsArtisanOutputToExistingOutput(): void
	{
		// Arrange
		$output = ['Previous step output'];

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn('Migration output line');

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert
		$this->assertCount(2, $result);
		$this->assertEquals('Previous step output', $result[0]);
		$this->assertEquals('Migration output line', $result[1]);
	}

	public function testHandleParsesMultiLineArtisanOutput(): void
	{
		// Arrange
		$output = [];
		$multiLineOutput = "Migrating: 2024_01_01_000000_create_table\nMigrated: 2024_01_01_000000_create_table\nMigration completed";

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn($multiLineOutput);

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert
		$this->assertCount(3, $result);
		$this->assertEquals('Migrating: 2024_01_01_000000_create_table', $result[0]);
		$this->assertEquals('Migrated: 2024_01_01_000000_create_table', $result[1]);
		$this->assertEquals('Migration completed', $result[2]);
	}

	public function testHandleIgnoresEmptyLinesInOutput(): void
	{
		// Arrange
		$output = [];
		$outputWithEmptyLines = "Line 1\n\nLine 2\n\n\nLine 3";

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn($outputWithEmptyLines);

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert - Empty lines should be filtered out by strToArray
		$this->assertCount(3, $result);
		$this->assertEquals('Line 1', $result[0]);
		$this->assertEquals('Line 2', $result[1]);
		$this->assertEquals('Line 3', $result[2]);
	}

	public function testHandleWithEmptyArtisanOutput(): void
	{
		// Arrange
		$output = ['Initial output'];

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn('');

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert - Empty output should not add any lines
		$this->assertCount(1, $result);
		$this->assertEquals('Initial output', $result[0]);
	}

	public function testHandleAlwaysCallsNextClosure(): void
	{
		// Arrange
		$output = [];
		$expectedOutput = ['Modified by next'];
		$nextCalled = false;

		Artisan::shouldReceive('call')
			->once()
			->with('migrate', ['--force' => true])
			->andReturn(0);

		Artisan::shouldReceive('output')
			->once()
			->andReturn('Migration output');

		$next = function (array $output) use (&$nextCalled, $expectedOutput): array {
			$nextCalled = true;

			return $expectedOutput;
		};

		// Act
		$result = $this->artisanMigrate->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}
}
