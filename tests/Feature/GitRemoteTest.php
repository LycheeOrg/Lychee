<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use Tests\TestCase;

class GitRemoteTest extends TestCase
{
	public function testCommits(): void
	{
		$remote = resolve(GitCommits::class);
		$data = $remote->fetchRemote(true);
		$countBehind = $remote->countBehind($data, 'fail');
		$this->assertEquals(30, $countBehind);

		$countBehind = $remote->countBehind([], 'fail');
		$this->assertFalse($countBehind);
	}

	public function testTags(): void
	{
		$remote = resolve(GitTags::class);
		$data = $remote->fetchRemote(false);
		$countBehind = $remote->countBehind($data, 'fail');
		$this->assertEquals(30, $countBehind);

		// This test will fail in the future when v4.6.2 is further than 30 versions away.
		$countBehind = $remote->countBehind($data, '296db84');
		$this->assertNotEquals(30, $countBehind);

		$tagName = $remote->getTagName($data, '296db84');
		$this->assertEquals('v4.6.2', $tagName);
	}
}