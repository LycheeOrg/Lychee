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

use App\Actions\InstallUpdate\Pipes\BranchCheck;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class BranchCheckTest extends AbstractTestCase
{
	private BranchCheck $branchCheck;
	private InstalledVersion|MockInterface $installedVersion;
	private GitHubVersion|MockInterface $githubVersion;

	protected function setUp(): void
	{
		parent::setUp();

		$this->installedVersion = \Mockery::mock(InstalledVersion::class);
		$this->githubVersion = \Mockery::mock(GitHubVersion::class);
		$this->branchCheck = new BranchCheck();
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleWithReleaseVersionCallsNext(): void
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
		$result = $this->branchCheck->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandleWithMasterBranchCallsNext(): void
	{
		// Arrange
		$output = ['Initial output'];
		$expectedOutput = ['Initial output', 'Next called'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->githubVersion->shouldReceive('hydrate')->once()->with(false);
		$this->githubVersion->shouldReceive('isMasterBranch')->once()->andReturn(true);

		$this->app->instance(InstalledVersion::class, $this->installedVersion);
		$this->app->instance(GitHubVersion::class, $this->githubVersion);

		$next = function (array $output) use (&$nextCalled, $expectedOutput): array {
			$nextCalled = true;

			return $expectedOutput;
		};

		// Act
		$result = $this->branchCheck->handle($output, $next);

		// Assert
		$this->assertTrue($nextCalled);
		$this->assertEquals($expectedOutput, $result);
	}

	public function testHandleWithNonMasterBranchReturnsOutputWithoutCallingNext(): void
	{
		// Arrange
		$output = ['Initial output'];
		$nextCalled = false;

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->githubVersion->shouldReceive('hydrate')->once()->with(false);
		$this->githubVersion->shouldReceive('isMasterBranch')->once()->andReturn(false);

		$this->app->instance(InstalledVersion::class, $this->installedVersion);
		$this->app->instance(GitHubVersion::class, $this->githubVersion);

		$next = function (array $output) use (&$nextCalled): array {
			$nextCalled = true;

			return $output;
		};

		// Act
		$result = $this->branchCheck->handle($output, $next);

		// Assert
		$this->assertFalse($nextCalled);
		$this->assertCount(2, $result);
		$this->assertEquals('Initial output', $result[0]);
		$this->assertEquals('Branch is not ' . GitHubVersion::MASTER, $result[1]);
	}

	public function testHandleWithEmptyOutputAndNonMasterBranch(): void
	{
		// Arrange
		$output = [];

		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->githubVersion->shouldReceive('hydrate')->once()->with(false);
		$this->githubVersion->shouldReceive('isMasterBranch')->once()->andReturn(false);

		$this->app->instance(InstalledVersion::class, $this->installedVersion);
		$this->app->instance(GitHubVersion::class, $this->githubVersion);

		$next = function (array $output): array {
			return $output;
		};

		// Act
		$result = $this->branchCheck->handle($output, $next);

		// Assert
		$this->assertCount(1, $result);
		$this->assertEquals('Branch is not ' . GitHubVersion::MASTER, $result[0]);
	}
}
