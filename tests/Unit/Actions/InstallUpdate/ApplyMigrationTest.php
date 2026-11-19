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

use App\Actions\InstallUpdate\ApplyMigration;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use Illuminate\Pipeline\Pipeline;
use Tests\AbstractTestCase;

class ApplyMigrationTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testRunOnlyMigrates(): void
	{
		$this->mockPipeline(['Migrating: 2026_10_03_000002_remove_online_update_configs']);

		$result = (new ApplyMigration())->run();

		$this->assertEquals(['Migrating: 2026_10_03_000002_remove_online_update_configs'], $result);
	}

	public function testRunRemovesAnsiColorCodes(): void
	{
		$this->mockPipeline([
			"\033[32mMigrating\033[0m",
			"\033[0;31;40mRed text on black background\033[0m",
			"\033[1;33;44mBold yellow on blue\033[0m",
		]);

		$result = (new ApplyMigration())->run();

		$this->assertEquals(['Migrating', 'Red text on black background', 'Bold yellow on blue'], $result);
	}

	public function testRunWithEmptyOutput(): void
	{
		$this->mockPipeline([]);

		$this->assertEmpty((new ApplyMigration())->run());
	}

	/**
	 * @param string[] $output
	 */
	private function mockPipeline(array $output): void
	{
		$pipeline = \Mockery::mock(Pipeline::class);
		$pipeline->shouldReceive('send')->once()->with([])->andReturnSelf();
		$pipeline->shouldReceive('through')->once()->with([ArtisanMigrate::class])->andReturnSelf();
		$pipeline->shouldReceive('thenReturn')->once()->andReturn($output);
		$this->app->instance(Pipeline::class, $pipeline);
	}
}
