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

namespace Tests\Unit\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Pipes\Infos\VersionInfo;
use App\DTO\Version;
use App\Enum\VersionChannelType;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class VersionInfoTest extends AbstractTestCase
{
	private InstalledVersion|MockInterface $installed_version;
	private FileVersion|MockInterface $file_version;
	private GitHubVersion|MockInterface $github_version;

	protected function setUp(): void
	{
		parent::setUp();

		$this->installed_version = \Mockery::mock(InstalledVersion::class);
		$this->file_version = \Mockery::mock(FileVersion::class);
		$this->file_version->shouldReceive('hydrate')->with(false);
		$this->file_version->shouldReceive('getVersion')->andReturn(Version::createFromString('7.10.0'));
		$this->github_version = \Mockery::mock(GitHubVersion::class);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testReleaseDoesNotTouchGit(): void
	{
		$this->installed_version->shouldReceive('isRelease')->andReturn(true);
		$this->github_version->shouldNotReceive('hydrate');

		self::assertSame(VersionChannelType::RELEASE, $this->versionInfo()->getChannelName());
	}

	public function testBranchCheckout(): void
	{
		$this->onGit(detached: false, branch: 'master', head: 'a1b2c3d');
		$this->github_version->shouldReceive('getBehindTest')->andReturn('3 commits behind master (2 hours ago)');

		$version_info = $this->versionInfo();
		self::assertSame(VersionChannelType::GIT, $version_info->getChannelName());

		$git_info = $version_info->getGitInfo();
		self::assertNotNull($git_info);
		self::assertSame('master (a1b2c3d)', $git_info->info);
		self::assertSame('3 commits behind master (2 hours ago)', $git_info->extra);
		self::assertSame('master (a1b2c3d) -- 3 commits behind master (2 hours ago)', $git_info->toString());
	}

	public function testDetachedHead(): void
	{
		$this->onGit(detached: true, branch: null, head: 'a1b2c3d');
		$this->github_version->shouldNotReceive('getBehindTest');

		$version_info = $this->versionInfo();
		self::assertSame(VersionChannelType::TAG, $version_info->getChannelName());

		$git_info = $version_info->getGitInfo();
		self::assertNotNull($git_info);
		self::assertSame('7.10.0 (a1b2c3d)', $git_info->info);
		self::assertSame('', $git_info->extra);
		self::assertSame('7.10.0 (a1b2c3d)', $git_info->toString());
	}

	public function testNoGitData(): void
	{
		$this->onGit(detached: false, branch: 'master', head: null);

		$version_info = $this->versionInfo();
		self::assertSame(VersionChannelType::GIT, $version_info->getChannelName());
		self::assertNull($version_info->getGitInfo());
	}

	public function testHandleWritesGitLine(): void
	{
		$this->onGit(detached: true, branch: null, head: 'a1b2c3d');
		$this->installed_version->shouldReceive('getVersion')->andReturn(Version::createFromString('7.10.0'));

		$data = [];
		$data = $this->versionInfo()->handle($data, fn (array $data) => $data);

		self::assertStringContainsString('(tag):', $data[0]);
		self::assertStringEndsWith('7.10.0 (a1b2c3d)', $data[0]);
	}

	private function onGit(bool $detached, ?string $branch, ?string $head): void
	{
		$this->installed_version->shouldReceive('isRelease')->andReturn(false);
		$this->github_version->shouldReceive('hydrate')->with(true, true);
		$this->github_version->shouldReceive('isDetached')->andReturn($detached);
		$this->github_version->local_branch = $branch;
		$this->github_version->local_head = $head;
	}

	private function versionInfo(): VersionInfo
	{
		$verify = \Mockery::mock(Verify::class);
		$verify->shouldReceive('get_status')->andReturn(Status::FREE_EDITION);
		$verify->shouldReceive('validate')->andReturn(true);

		return new VersionInfo($this->installed_version, $this->file_version, $this->github_version, $verify);
	}
}
