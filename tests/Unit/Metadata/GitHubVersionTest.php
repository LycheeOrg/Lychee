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

namespace Tests\Unit\Metadata;

use App\Facades\Helpers;
use App\Metadata\Json\CommitsRequest;
use App\Metadata\Json\TagsRequest;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class GitHubVersionTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHydrateWithNoGitDirectory(): void
	{
		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		File::shouldReceive('isReadable')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		Log::shouldReceive('warning')
			->once()
			->with(\Mockery::pattern('/Could not read.*\.git\/HEAD/'));

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertNull($version->local_branch);
		$this->assertNull($version->local_head);
	}

	public function testHydrateWithGitCommitsMode(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertEquals('master', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
	}

	public function testHydrateWithGitCommitsModeAndRemote(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
			(object) ['sha' => 'b2c3d4e5f6g7h8i9j0k1'],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('master', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
	}

	public function testHydrateWithGitTagsMode(): void
	{
		$headContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($headContent);

		$mockTags = \Mockery::mock(GitTags::class);
		$this->app->instance(GitTags::class, $mockTags);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertNull($version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
	}

	public function testHydrateWithGitTagsModeAndRemote(): void
	{
		$headContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) [
				'name' => 'v4.6.3',
				'commit' => (object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
			],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($headContent);

		$mockRequest = \Mockery::mock(TagsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(TagsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('v4.6.3', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
	}

	public function testIsReleaseWithGitTags(): void
	{
		$headContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) [
				'name' => 'v4.6.3',
				'commit' => (object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
			],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($headContent);

		$mockRequest = \Mockery::mock(TagsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(TagsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertTrue($version->isRelease());
	}

	public function testIsReleaseWithGitCommits(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertFalse($version->isRelease());
	}

	public function testIsMasterBranchWithGitTags(): void
	{
		$headContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($headContent);

		$mockTags = \Mockery::mock(GitTags::class);
		$this->app->instance(GitTags::class, $mockTags);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertTrue($version->isMasterBranch());
	}

	public function testIsMasterBranchWithGitCommitsOnMaster(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertTrue($version->isMasterBranch());
	}

	public function testIsMasterBranchWithGitCommitsOnFeatureBranch(): void
	{
		$branchContent = "ref: refs/heads/feature-branch\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/feature-branch'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/feature-branch'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertFalse($version->isMasterBranch());
	}

	public function testIsUpToDateWhenBehindIsFalse(): void
	{
		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		File::shouldReceive('isReadable')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		Log::shouldReceive('warning')
			->once();

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertTrue($version->isUpToDate());
	}

	public function testIsUpToDateWhenBehindIsZero(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertTrue($version->isUpToDate());
	}

	public function testIsNotUpToDateWhenBehind(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) ['sha' => 'b2c3d4e5f6g7h8i9j0k1'],
			(object) ['sha' => 'c3d4e5f6g7h8i9j0k1l2'],
			(object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertFalse($version->isUpToDate());
	}

	public function testGetBehindTextWhenCouldNotCompare(): void
	{
		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		File::shouldReceive('isReadable')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(false);

		Log::shouldReceive('warning')
			->once();

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertEquals('Could not compare.', $version->getBehindTest());
	}

	public function testGetBehindTextWhenUpToDate(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 hours ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('Up to date (2 hours ago).', $version->getBehindTest());
	}

	public function testGetBehindTextWhenBehindByTwo(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = [
			(object) ['sha' => 'b2c3d4e5f6g7h8i9j0k1'],
			(object) ['sha' => 'c3d4e5f6g7h8i9j0k1l2'],
			(object) ['sha' => 'a1b2c3d4e5f6g7h8i9j0'],
		];

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('1 day ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('2 commits behind b2c3d4e (1 day ago)', $version->getBehindTest());
	}

	public function testGetBehindTextWhenBehindByThirty(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";
		$remoteData = array_fill(0, 30, (object) ['sha' => 'different']);

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockRequest = \Mockery::mock(CommitsRequest::class);
		$mockRequest->shouldReceive('get_json')
			->with(true)
			->andReturn($remoteData);
		$mockRequest->shouldReceive('get_age_text')
			->andReturn('2 weeks ago');

		$this->app->instance(CommitsRequest::class, $mockRequest);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('More than 30 commits behind (2 weeks ago).', $version->getBehindTest());
	}

	public function testHasPermissionsWithGitCommitsAndFullPermissions(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		Helpers::shouldReceive('hasFullPermissions')
			->with(base_path('.git'))
			->once()
			->andReturn(true);

		Helpers::shouldReceive('hasPermissions')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertTrue($version->hasPermissions());
	}

	public function testHasPermissionsWithGitCommitsAndNoFullPermissions(): void
	{
		$branchContent = "ref: refs/heads/master\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		Helpers::shouldReceive('hasFullPermissions')
			->with(base_path('.git'))
			->once()
			->andReturn(false);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertFalse($version->hasPermissions());
	}

	public function testHasPermissionsWithGitTags(): void
	{
		$headContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($headContent);

		$mockTags = \Mockery::mock(GitTags::class);
		$this->app->instance(GitTags::class, $mockTags);

		Helpers::shouldReceive('hasFullPermissions')
			->with(base_path('.git'))
			->once()
			->andReturn(true);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertFalse($version->hasPermissions());
	}

	public function testHydrateWithNonReadableCommitFile(): void
	{
		$branchContent = "ref: refs/heads/master\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(false);

		File::shouldReceive('isReadable')
			->with(base_path('.git/refs/heads/master'))
			->once()
			->andReturn(false);

		Log::shouldReceive('warning')
			->once()
			->with(\Mockery::pattern('/Could not read.*\.git\/refs\/heads\/master/'));

		$mockCommits = \Mockery::mock(GitCommits::class);
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(false, true);

		$this->assertEquals('master', $version->local_branch);
		$this->assertNull($version->local_head);
	}

	public function testHydrateRemoteDoesNotFetchOnFeatureBranch(): void
	{
		$branchContent = "ref: refs/heads/feature-branch\n";
		$commitContent = "a1b2c3d4e5f6g7h8i9j0\n";

		File::shouldReceive('exists')
			->with(base_path('.git/HEAD'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/HEAD'))
			->twice()
			->andReturn($branchContent);

		File::shouldReceive('exists')
			->with(base_path('.git/refs/heads/feature-branch'))
			->once()
			->andReturn(true);

		File::shouldReceive('get')
			->with(base_path('.git/refs/heads/feature-branch'))
			->once()
			->andReturn($commitContent);

		$mockCommits = \Mockery::mock(GitCommits::class);
		$mockCommits->shouldNotReceive('fetchRemote');
		$this->app->instance(GitCommits::class, $mockCommits);

		$version = new GitHubVersion();
		$version->hydrate(true, true);

		$this->assertEquals('feature-branch', $version->local_branch);
	}
}
