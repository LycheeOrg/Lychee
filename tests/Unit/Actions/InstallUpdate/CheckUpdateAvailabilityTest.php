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

use App\Actions\InstallUpdate\CheckUpdateAvailability;
use App\DTO\UpdateAvailability;
use App\DTO\Version;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class CheckUpdateAvailabilityTest extends AbstractTestCase
{
	private FileVersion|MockInterface $file_version;
	private GitHubVersion|MockInterface $github_version;
	private ConfigManager|MockInterface $config_manager;

	protected function setUp(): void
	{
		parent::setUp();

		Config::set('features.update-check', true);
		$this->file_version = \Mockery::mock(FileVersion::class);
		$this->github_version = \Mockery::mock(GitHubVersion::class);
		$this->config_manager = \Mockery::mock(ConfigManager::class);
		$this->config_manager->shouldReceive('getValueAsBool')->with('check_for_updates')->andReturn(true)->byDefault();
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testFeatureFlagOffDoesNotCheck(): void
	{
		Config::set('features.update-check', false);
		$this->file_version->shouldNotReceive('hydrate');
		$this->github_version->shouldNotReceive('hydrate');

		$this->assertDisabled($this->action()->get());
	}

	public function testConfigOffDoesNotCheck(): void
	{
		$this->config_manager->shouldReceive('getValueAsBool')->with('check_for_updates')->andReturn(false);
		$this->file_version->shouldNotReceive('hydrate');
		$this->github_version->shouldNotReceive('hydrate');

		$this->assertDisabled($this->action()->get());
	}

	public function testReleaseUpdateOnly(): void
	{
		$this->arrangeFile('7.10.0', '7.11.0', false);
		$this->arrangeGit(true, 0);

		$result = $this->action()->get();

		self::assertTrue($result->enabled);
		self::assertTrue($result->is_new_release_available);
		self::assertFalse($result->is_git_update_available);
		self::assertSame(0, $result->commits_behind);
		self::assertSame('7.10.0', $result->current_version);
		self::assertSame('7.11.0', $result->latest_version);
	}

	public function testGitUpdateOnly(): void
	{
		$this->arrangeFile('7.10.0', '7.10.0', true);
		$this->arrangeGit(false, 3);

		$result = $this->action()->get();

		self::assertTrue($result->enabled);
		self::assertFalse($result->is_new_release_available);
		self::assertTrue($result->is_git_update_available);
		self::assertSame(3, $result->commits_behind);
		self::assertSame('7.10.0', $result->current_version);
		self::assertSame('7.10.0', $result->latest_version);
	}

	public function testUnknownGitDistanceIsNotAnUpdate(): void
	{
		$this->arrangeFile('7.10.0', null, true);
		$this->arrangeGit(true, false);

		$result = $this->action()->get();

		self::assertTrue($result->enabled);
		self::assertFalse($result->is_new_release_available);
		self::assertFalse($result->is_git_update_available);
		self::assertNull($result->commits_behind);
		self::assertNull($result->latest_version);
	}

	public function testBothUpdates(): void
	{
		$this->arrangeFile('7.9.0', '7.10.0', false);
		$this->arrangeGit(false, 42);

		$result = $this->action()->get();

		self::assertTrue($result->is_new_release_available);
		self::assertTrue($result->is_git_update_available);
		self::assertSame(42, $result->commits_behind);
	}

	private function action(): CheckUpdateAvailability
	{
		return new CheckUpdateAvailability($this->file_version, $this->github_version, $this->config_manager);
	}

	private function arrangeFile(string $current, ?string $remote, bool $is_up_to_date): void
	{
		$this->file_version->shouldReceive('hydrate')->once();
		$this->file_version->shouldReceive('isUpToDate')->andReturn($is_up_to_date);
		$this->file_version->shouldReceive('getVersion')->andReturn(Version::createFromString($current));
		$this->file_version->remote_version = $remote === null ? null : Version::createFromString($remote);
	}

	private function arrangeGit(bool $is_up_to_date, int|false $count_behind): void
	{
		$this->github_version->shouldReceive('hydrate')->once();
		$this->github_version->shouldReceive('isUpToDate')->andReturn($is_up_to_date);
		$this->github_version->shouldReceive('getCountBehind')->andReturn($count_behind);
	}

	private function assertDisabled(UpdateAvailability $result): void
	{
		self::assertFalse($result->enabled);
		self::assertFalse($result->is_new_release_available);
		self::assertFalse($result->is_git_update_available);
		self::assertNull($result->commits_behind);
		self::assertNull($result->current_version);
		self::assertNull($result->latest_version);
	}
}
