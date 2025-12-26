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

namespace Tests\Unit\Actions\InstallUpdate;

use App\Actions\InstallUpdate\ApplyUpdate;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use App\Actions\InstallUpdate\Pipes\BranchCheck;
use App\Actions\InstallUpdate\Pipes\ComposerCall;
use App\Actions\InstallUpdate\Pipes\GitPull;
use Illuminate\Pipeline\Pipeline;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class ApplyUpdateTest extends AbstractTestCase
{
	private Pipeline|MockInterface $pipeline;
	private BranchCheck|MockInterface $branchCheck;
	private GitPull|MockInterface $gitPull;
	private ArtisanMigrate|MockInterface $artisanMigrate;
	private ComposerCall|MockInterface $composerCall;

	protected function setUp(): void
	{
		parent::setUp();

		$this->pipeline = \Mockery::mock(Pipeline::class);
		$this->branchCheck = \Mockery::mock(BranchCheck::class);
		$this->gitPull = \Mockery::mock(GitPull::class);
		$this->artisanMigrate = \Mockery::mock(ArtisanMigrate::class);
		$this->composerCall = \Mockery::mock(ComposerCall::class);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testRunExecutesPipelineAndReturnsOutput(): void
	{
		// Arrange
		$expectedOutput = [
			'Step 1: Checking branch',
			'Step 2: Running git pull',
			'Step 3: Running migrations',
			'Step 4: Running composer',
		];

		$this->pipeline
			->shouldReceive('send')
			->once()
			->with([])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('through')
			->once()
			->with([
				BranchCheck::class,
				GitPull::class,
				ArtisanMigrate::class,
				ComposerCall::class,
			])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('thenReturn')
			->once()
			->andReturn($expectedOutput);

		// Mock the app() function to return our mocked pipeline
		$this->app->instance(Pipeline::class, $this->pipeline);

		// Act
		$applyUpdate = new ApplyUpdate();
		$result = $applyUpdate->run();

		// Assert
		$this->assertEquals($expectedOutput, $result);
	}

	public function testRunRemovesAnsiColorCodes(): void
	{
		// Arrange
		$outputWithAnsi = [
			"\033[32mStep 1: Checking branch\033[0m",
			"\033[33mStep 2: Running git pull\033[0m",
			"\033[34;1mStep 3: Running migrations\033[0m",
		];

		$expectedCleanOutput = [
			'Step 1: Checking branch',
			'Step 2: Running git pull',
			'Step 3: Running migrations',
		];

		$this->pipeline
			->shouldReceive('send')
			->once()
			->with([])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('through')
			->once()
			->with([
				BranchCheck::class,
				GitPull::class,
				ArtisanMigrate::class,
				ComposerCall::class,
			])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('thenReturn')
			->once()
			->andReturn($outputWithAnsi);

		// Mock the app() function to return our mocked pipeline
		$this->app->instance(Pipeline::class, $this->pipeline);

		// Act
		$applyUpdate = new ApplyUpdate();
		$result = $applyUpdate->run();

		// Assert
		$this->assertEquals($expectedCleanOutput, $result);
	}

	public function testRunWithEmptyOutput(): void
	{
		// Arrange
		$expectedOutput = [];

		$this->pipeline
			->shouldReceive('send')
			->once()
			->with([])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('through')
			->once()
			->with([
				BranchCheck::class,
				GitPull::class,
				ArtisanMigrate::class,
				ComposerCall::class,
			])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('thenReturn')
			->once()
			->andReturn($expectedOutput);

		// Mock the app() function to return our mocked pipeline
		$this->app->instance(Pipeline::class, $this->pipeline);

		// Act
		$applyUpdate = new ApplyUpdate();
		$result = $applyUpdate->run();

		// Assert
		$this->assertEmpty($result);
	}

	public function testRunWithComplexAnsiCodes(): void
	{
		// Arrange
		$outputWithComplexAnsi = [
			"\033[0;31;40mRed text on black background\033[0m",
			"\033[1;33;44mBold yellow on blue\033[0m",
			"\033[38;5;82mExtended color\033[0m",
		];

		$expectedCleanOutput = [
			'Red text on black background',
			'Bold yellow on blue',
			'Extended color',
		];

		$this->pipeline
			->shouldReceive('send')
			->once()
			->with([])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('through')
			->once()
			->with([
				BranchCheck::class,
				GitPull::class,
				ArtisanMigrate::class,
				ComposerCall::class,
			])
			->andReturnSelf();

		$this->pipeline
			->shouldReceive('thenReturn')
			->once()
			->andReturn($outputWithComplexAnsi);

		// Mock the app() function to return our mocked pipeline
		$this->app->instance(Pipeline::class, $this->pipeline);

		// Act
		$applyUpdate = new ApplyUpdate();
		$result = $applyUpdate->run();

		// Assert
		$this->assertEquals($expectedCleanOutput, $result);
	}
}
