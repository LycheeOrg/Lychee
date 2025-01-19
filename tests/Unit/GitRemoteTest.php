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

namespace Tests\Unit;

use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use Illuminate\Support\Facades\File;
use function Safe\json_decode;
use Tests\AbstractTestCase;

class GitRemoteTest extends AbstractTestCase
{
	public function testCommits(): void
	{
		$remote = resolve(GitCommits::class);
		$data = $remote->fetchRemote(true);

		// due to api call limitations, $data can be empty...
		$data = json_decode(File::get(base_path('tests/Samples/commits.json')));

		$countBehind = $remote->countBehind($data, 'fail');
		self::assertEquals(30, $countBehind);

		$countBehind = $remote->countBehind([], 'fail');
		self::assertFalse($countBehind);

		$countBehind = $remote->countBehind($data, 'f3854cf');
		self::assertEquals(1, $countBehind);

		self::assertEquals('commits', $remote->getType());
	}

	public function testTags(): void
	{
		$remote = resolve(GitTags::class);
		$data = $remote->fetchRemote(false);

		// due to api call limitations, $data can be empty...
		$data = json_decode(File::get(base_path('tests/Samples/tags.json')));

		$countBehind = $remote->countBehind($data, 'fail');
		self::assertEquals(30, $countBehind);

		$countBehind = $remote->countBehind($data, '1144961');
		self::assertEquals(4, $countBehind);

		// This test will fail in the future when v4.6.2 is further than 30 versions away.
		$countBehind = $remote->countBehind($data, '296db84');
		self::assertNotEquals(30, $countBehind);

		$tagName = $remote->getTagName($data, '296db84');
		self::assertEquals('v4.6.2', $tagName);

		$tagName = $remote->getTagName([], 'fail');
		self::assertEquals('', $tagName);

		self::assertEquals('tags', $remote->getType());
	}
}