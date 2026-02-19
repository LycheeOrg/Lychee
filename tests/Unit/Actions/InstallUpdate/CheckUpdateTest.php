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

namespace Tests\Unit\Actions\InstallUpdate;

use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\Actions\InstallUpdate\CheckUpdate;
use App\Enum\UpdateStatus;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AbstractTestCase;

class CheckUpdateTest extends AbstractTestCase
{
	private GitHubVersion|MockInterface $githubVersion;
	private InstalledVersion|MockInterface $installedVersion;
	private FileVersion|MockInterface $fileVersion;

	protected function setUp(): void
	{
		parent::setUp();

		$this->githubVersion = \Mockery::mock(GitHubVersion::class);
		$this->installedVersion = \Mockery::mock(InstalledVersion::class);
		$this->fileVersion = \Mockery::mock(FileVersion::class);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeReturnsNotMasterWhenNotOnMasterBranch(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);

		// Mock the static call to UpdatableCheck::assertUpdatability() to throw exception
		$mock = \Mockery::mock('alias:' . UpdatableCheck::class);
		$mock->shouldReceive('assertUpdatability')
			->andThrow(new \Exception('Not on master branch'));

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::NOT_MASTER, $result);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeReturnsUpToDateWhenGitIsUpToDate(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->githubVersion->shouldReceive('isUpToDate')->once()->andReturn(true);

		$mock = \Mockery::mock('alias:' . UpdatableCheck::class);
		$mock->shouldReceive('assertUpdatability')
			->andReturn(null);

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::UP_TO_DATE, $result);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeReturnsNotUpToDateWhenGitIsBehind(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(false);
		$this->githubVersion->shouldReceive('isUpToDate')->once()->andReturn(false);

		$mock = \Mockery::mock('alias:' . UpdatableCheck::class);
		$mock->shouldReceive('assertUpdatability')
			->andReturn(null);

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::NOT_UP_TO_DATE, $result);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeWithReleaseVersionChecksFileVersion(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(true);
		$this->fileVersion->shouldReceive('isUpToDate')->once()->andReturn(true);

		$mock = \Mockery::mock('alias:' . MigrationCheck::class);
		$mock->shouldReceive('isUpToDate')
			->once()
			->andReturn(true);

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::UP_TO_DATE, $result);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeWithReleaseVersionReturnsNotUpToDateWhenFileIsBehind(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(true);
		$this->fileVersion->shouldReceive('isUpToDate')->once()->andReturn(false);

		$mock = \Mockery::mock('alias:' . MigrationCheck::class);
		$mock->shouldReceive('isUpToDate')
			->once()
			->andReturn(true);

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::NOT_UP_TO_DATE, $result);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testGetCodeWithReleaseVersionReturnsRequireMigrationWhenMigrationNeeded(): void
	{
		// Arrange
		$this->githubVersion->shouldReceive('hydrate')->once();
		$this->fileVersion->shouldReceive('hydrate')->once();
		$this->installedVersion->shouldReceive('isRelease')->once()->andReturn(true);

		$mock = \Mockery::mock('alias:' . MigrationCheck::class);
		$mock->shouldReceive('isUpToDate')
			->once()
			->andReturn(false);

		// Act
		$checkUpdate = new CheckUpdate(
			$this->githubVersion,
			$this->installedVersion,
			$this->fileVersion
		);
		$result = $checkUpdate->getCode();

		// Assert
		$this->assertEquals(UpdateStatus::REQUIRE_MIGRATION, $result);
	}
}
