<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

use App\Actions\InstallUpdate\Pipes\GitPull;
use App\Assets\CommandExecutor;
use App\Facades\Helpers;
use App\Metadata\Versions\InstalledVersion;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class GitPullTest extends AbstractTestCase
{
	private GitPull $gitPull;
	private InstalledVersion|MockInterface $installedVersion;
	private CommandExecutor|MockInterface $commandExecutor;

	protected function setUp(): void
	{
		parent::setUp();

		$this->installedVersion = \Mockery::mock(InstalledVersion::class);
		$this->commandExecutor = \Mockery::mock(CommandExecutor::class);
		$this->gitPull = new GitPull($this->commandExecutor);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleWithReleaseVersionSkipsGitPull(): void
	{
		// Arrange
		$output = ['Initial output'];
		$expectedOutput = ['Initial output', 'Next called'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(true);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		$next = function (array $output) use (&$nextCalled, $expectedOutput): array {
			$nextCalled = true;

			return $expectedOutput;
		};

		// Act
		$result = $this->gitPull->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandleWithExecNotAvailableReturnsOutputWithoutCallingNext(): void
	{
		// Arrange
		$output = ['Initial output'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(false);

		$next = function (array $output) use (&$nextCalled): array {
			$nextCalled = true;

			return $output;
		};

		// Act
		$result = $this->gitPull->handle($output, $next);

		// Assert
		$this->assertFalse($nextCalled);
		$this->assertEquals($output, $result);
	}

	public function testHandleWithExecAvailableExecutesGitPullAndCallsNext(): void
	{
		// Arrange
		$output = [];
		$expectedOutput = ['Git pull output', 'Additional output'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(true);

		// Mock config to return git pull URL
		config(['urls.git.pull' => 'https://github.com/LycheeOrg/Lychee.git']);

		// Mock the CommandExecutor exec method to simulate git pull execution
		$this->commandExecutor->shouldReceive('exec')
			->once()
			->with('git pull --rebase https://github.com/LycheeOrg/Lychee.git master 2>&1', \Mockery::on(function (&$output) {
				// Simulate exec populating the output array
				$output = ['Git pull output'];

				return true;
			}))
			->andReturnUsing(function ($command, &$output) {
				$output = ['Git pull output'];
			});

		$next = function (array $output) use (&$nextCalled, $expectedOutput): array {
			$nextCalled = true;

			// In the real implementation, exec would have populated $output
			return $expectedOutput;
		};

		// Act
		$result = $this->gitPull->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandlePreservesExistingOutputWhenExecNotAvailable(): void
	{
		// Arrange
		$output = ['Line 1', 'Line 2', 'Line 3'];

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(false);

		$next = function (array $output): array {
			return array_merge($output, ['Should not be called']);
		};

		// Act
		$result = $this->gitPull->handle($output, $next);

		// Assert
		$this->assertCount(3, $result);
		$this->assertEquals('Line 1', $result[0]);
		$this->assertEquals('Line 2', $result[1]);
		$this->assertEquals('Line 3', $result[2]);
	}
}
