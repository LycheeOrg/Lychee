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

use App\Actions\InstallUpdate\Pipes\ComposerCall;
use App\Assets\CommandExecutor;
use App\Facades\Helpers;
use App\Metadata\Versions\InstalledVersion;
use App\Repositories\ConfigManager;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class ComposerCallTest extends AbstractTestCase
{
	private ComposerCall $composerCall;
	private InstalledVersion|MockInterface $installedVersion;
	private CommandExecutor|MockInterface $commandExecutor;
	private ConfigManager|MockInterface $configManager;

	protected function setUp(): void
	{
		parent::setUp();

		$this->installedVersion = \Mockery::mock(InstalledVersion::class);
		$this->commandExecutor = \Mockery::mock(CommandExecutor::class);
		$this->configManager = \Mockery::mock(ConfigManager::class);
		$this->composerCall = new ComposerCall($this->configManager, $this->commandExecutor);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleWithReleaseVersionSkipsComposerCall(): void
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
		$result = $this->composerCall->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandleWithExecNotAvailableCallsNext(): void
	{
		// Arrange
		$output = ['Initial output'];
		$expectedOutput = ['Initial output', 'Next called'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->installedVersion->shouldReceive('isDev')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(false);

		$next = function (array $output) use (&$nextCalled, $expectedOutput): array {
			$nextCalled = true;

			return $expectedOutput;
		};

		// Act
		$result = $this->composerCall->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandleWithComposerUpdateDisabledAddsWarningMessages(): void
	{
		// Arrange
		$output = [];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->installedVersion->shouldReceive('isDev')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(true);

		// Mock ConfigManager to return false for apply_composer_update
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('apply_composer_update')
			->andReturn(false);

		// CommandExecutor should not be called when composer update is disabled
		$this->commandExecutor->shouldNotReceive('putenv');
		$this->commandExecutor->shouldNotReceive('chdir');
		$this->commandExecutor->shouldNotReceive('exec');

		$next = function (array $output) use (&$nextCalled): array {
			$nextCalled = true;

			return $output;
		};

		// Act
		$result = $this->composerCall->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertCount(3, $result);
		$this->assertEquals('Composer update are always dangerous when automated.', $result[0]);
		$this->assertEquals('So we did not execute it.', $result[1]);
		$this->assertEquals('If you want to have composer update applied, please set the setting to 1 at your own risk.', $result[2]);
	}

	public function testHandleWithComposerUpdateDisabledPreservesExistingOutput(): void
	{
		// Arrange
		$output = ['Previous step output'];

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->installedVersion->shouldReceive('isDev')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(true);

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('apply_composer_update')
			->andReturn(false);

		// CommandExecutor should not be called when composer update is disabled
		$this->commandExecutor->shouldNotReceive('putenv');
		$this->commandExecutor->shouldNotReceive('chdir');
		$this->commandExecutor->shouldNotReceive('exec');

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->composerCall->handle($output, $next);

		// Assert
		$this->assertCount(4, $result);
		$this->assertEquals('Previous step output', $result[0]);
		$this->assertEquals('Composer update are always dangerous when automated.', $result[1]);
	}

	public function testHandleChecksDevModeForComposerFlags(): void
	{
		// Arrange
		$output = [];

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->installedVersion->shouldReceive('isDev')->once()->andReturn(true);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(true);

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('apply_composer_update')
			->andReturn(false);

		// CommandExecutor should not be called when composer update is disabled
		$this->commandExecutor->shouldNotReceive('putenv');
		$this->commandExecutor->shouldNotReceive('chdir');
		$this->commandExecutor->shouldNotReceive('exec');

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->composerCall->handle($output, $next);

		// Assert - Verify isDev was called (which would affect the $no_dev flag)
		// The actual execution is mocked out, but we verify the logic path
		$this->assertCount(3, $result);
	}

	public function testHandleWithProductionModeChecksDevFlag(): void
	{
		// Arrange
		$output = [];

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->installedVersion->shouldReceive('isDev')->once()->andReturn(false);
		$this->app->instance(InstalledVersion::class, $this->installedVersion);

		Helpers::shouldReceive('isExecAvailable')->once()->andReturn(true);

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('apply_composer_update')
			->andReturn(false);

		// CommandExecutor should not be called when composer update is disabled
		$this->commandExecutor->shouldNotReceive('putenv');
		$this->commandExecutor->shouldNotReceive('chdir');
		$this->commandExecutor->shouldNotReceive('exec');

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->composerCall->handle($output, $next);

		// Assert - Verify the flow works for production mode
		$this->assertCount(3, $result);
		$this->assertStringContainsString('Composer update are always dangerous', $result[0]);
	}
}
