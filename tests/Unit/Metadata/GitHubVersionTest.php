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

use App\Metadata\Json\CompareRequest;
use App\Metadata\Versions\GitHubVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use function Safe\file_get_contents;
use function Safe\json_decode;
use Tests\AbstractTestCase;

class GitHubVersionTest extends AbstractTestCase
{
	private const SHA = "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0\n";

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testNoGitDirectory(): void
	{
		File::shouldReceive('isReadable')->with(base_path('.git/HEAD'))->once()->andReturn(false);
		Log::shouldReceive('warning')->once()->with(\Mockery::pattern('/Could not read.*\.git\/HEAD/'));
		$this->expectNoCompare();

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertNull($version->local_branch);
		$this->assertNull($version->local_head);
		$this->assertFalse($version->isDetached());
		$this->assertFalse($version->isMasterBranch());
		$this->assertTrue($version->isUpToDate());
		$this->assertEquals('Could not compare.', $version->getBehindTest());
	}

	public function testMasterUpToDate(): void
	{
		$this->onBranch('master', self::SHA);
		$this->expectCompare((object) ['status' => 'identical', 'ahead_by' => 0, 'behind_by' => 0]);

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertEquals('master', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
		$this->assertTrue($version->isMasterBranch());
		$this->assertFalse($version->isDetached());
		$this->assertSame(0, $version->getCountBehind());
		$this->assertTrue($version->isUpToDate());
		$this->assertEquals('Up to date (2 hours ago).', $version->getBehindTest());
	}

	public function testMasterBehind(): void
	{
		$this->onBranch('master', self::SHA);
		// Read directly: the File facade is mocked.
		$this->expectCompare(json_decode(file_get_contents(base_path('tests/Samples/compare.json'))));

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertSame(42, $version->getCountBehind());
		$this->assertFalse($version->isUpToDate());
		$this->assertEquals('42 commits behind master (2 hours ago)', $version->getBehindTest());
	}

	public function testMasterCompareFails(): void
	{
		$this->onBranch('master', self::SHA);
		$this->expectCompare(null);

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertFalse($version->getCountBehind());
		$this->assertTrue($version->isUpToDate());
		$this->assertEquals('Could not compare.', $version->getBehindTest());
	}

	public function testMasterCompareMalformed(): void
	{
		$this->onBranch('master', self::SHA);
		$this->expectCompare((object) ['status' => 'ahead', 'ahead_by' => '3']);

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertFalse($version->getCountBehind());
	}

	public function testCompareHonoursCacheFlag(): void
	{
		$this->onBranch('master', self::SHA);
		$this->expectCompare((object) ['ahead_by' => 1], use_cache: false);

		$version = new GitHubVersion();
		$version->hydrate(with_remote: true, use_cache: false);

		$this->assertSame(1, $version->getCountBehind());
	}

	public function testFeatureBranchDoesNotCompare(): void
	{
		$this->onBranch('feature/foo', self::SHA);
		$this->expectNoCompare();

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertEquals('feature/foo', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
		$this->assertFalse($version->isMasterBranch());
		$this->assertFalse($version->isDetached());
		$this->assertEquals('Could not compare.', $version->getBehindTest());
	}

	public function testDetachedHeadDoesNotCompare(): void
	{
		File::shouldReceive('isReadable')->with(base_path('.git/HEAD'))->once()->andReturn(true);
		File::shouldReceive('get')->with(base_path('.git/HEAD'))->once()->andReturn(self::SHA);
		$this->expectNoCompare();

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertNull($version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
		$this->assertTrue($version->isDetached());
		$this->assertFalse($version->isMasterBranch());
		$this->assertTrue($version->isUpToDate());
	}

	public function testWithoutRemoteDoesNotCompare(): void
	{
		$this->onBranch('master', self::SHA);
		$this->expectNoCompare();

		$version = new GitHubVersion();
		$version->hydrate(with_remote: false);

		$this->assertEquals('master', $version->local_branch);
		$this->assertEquals('a1b2c3d', $version->local_head);
		$this->assertFalse($version->getCountBehind());
	}

	public function testUnreadableRefFile(): void
	{
		$this->onBranch('master', null);
		Log::shouldReceive('warning')->once()->with(\Mockery::pattern('/Could not read.*\.git\/refs\/heads\/master/'));
		$this->expectNoCompare();

		$version = new GitHubVersion();
		$version->hydrate();

		$this->assertEquals('master', $version->local_branch);
		$this->assertNull($version->local_head);
	}

	/**
	 * Arrange .git/HEAD pointing to $branch, whose ref file holds $sha (null: unreadable).
	 */
	private function onBranch(string $branch, ?string $sha): void
	{
		$ref = base_path('.git/refs/heads/' . $branch);
		File::shouldReceive('isReadable')->with(base_path('.git/HEAD'))->once()->andReturn(true);
		File::shouldReceive('get')->with(base_path('.git/HEAD'))->once()->andReturn('ref: refs/heads/' . $branch . "\n");
		File::shouldReceive('isReadable')->with($ref)->once()->andReturn($sha !== null);
		if ($sha !== null) {
			File::shouldReceive('get')->with($ref)->once()->andReturn($sha);
		}
	}

	/**
	 * Bind a CompareRequest mock for the local sha a1b2c3d answering with $json (null: request failed).
	 */
	private function expectCompare(mixed $json, bool $use_cache = true): void
	{
		$mock = \Mockery::mock(CompareRequest::class);
		$mock->shouldReceive('get_json')->with($use_cache)->once()->andReturn($json);
		$mock->shouldReceive('get_age_text')->andReturn('2 hours ago');

		$this->app->bind(CompareRequest::class, function ($app, array $params) use ($mock) {
			self::assertSame('a1b2c3d', $params['local_sha'] ?? null);

			return $mock;
		});
	}

	private function expectNoCompare(): void
	{
		$this->app->bind(CompareRequest::class, fn () => self::fail('CompareRequest must not be resolved.'));
	}
}
